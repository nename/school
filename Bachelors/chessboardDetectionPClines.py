#!/usr/bin/env python
"""
Module detects chessboard using line detection. Lines are detected by parallel coordinates implemented by Roman
Juránek.

https://github.com/RomanJuranek/pclines-python

References
----------
[1] Dubska et al, PCLines - Line detection with parallel coordinates, CVPR 2011

author: Tomáš Hampl - xhampl10 (xhampl10@stud.fit.vutbr.cz)
"""

import math
import cv2 as cv
import numpy as np
import statistics
import sys

from pclines import PCLines
from pclines import utils


class ChessBoardDetection:
    """
    Class encapsulates chessboard detection using parallel coordinates.
    """
    def __init__(self, d=256, min_dist=1, prominence=2, t=0.35):
        """
        Init function.

        :param d: accumulator size
        :param min_dist: minimal dist detection
        :param prominence: point prominence in acc
        :param t: minimal threshold intensity
        """
        self.img_shape = [600, 600]
        self.path = ""      # image path
        self.pressed = False    # mouse press
        self.middle_point = []  # middle point of the chessboard

        # PC lines parameters
        self.d = d      # accumulator size
        self.min_dist = min_dist      # minimal dist
        self.prominence = prominence    # point prominence
        self.t = t      # minimal intensity threshold

    def detect_lines(self, path):
        """
        Functions detects lines using parallel coordinates.

        :param path: image path
        :return: X, Y touples
        """
        img = cv.imread(path, cv.IMREAD_GRAYSCALE)

        img = cv.resize(img, tuple(self.img_shape))

        # filter sigma
        img = cv.bilateralFilter(img, 3, 25, 75)

        # detect edges
        edges = cv.Canny(img, 150, 250)

        # author: Roman Juránek https://github.com/RomanJuranek/pclines-python

        # locations of edges
        r, c = np.nonzero(edges > 0.5)

        # Matrix with edges
        x = np.array([c, r], "i").T
        weights = edges[r, c]
        bbox = (0, 0, self.img_shape[1], self.img_shape[0])
        d = self.d

        # create new accumulator
        pclines = PCLines(bbox, d)

        pclines.insert(x, weights)

        p, w = pclines.find_peaks(min_dist=self.min_dist, prominence=self.prominence, t=self.t)

        h = pclines.inverse(p)

        x, y = utils.line_segments_from_homogeneous(h, bbox)

        # Code ends

        return x, y

    @staticmethod
    def check_line_existence(arr, point, key):
        """
        Helper function to check if point is in already in array to prevent same points.

        :param arr: array
        :param point: point
        :param key: key that determines if line is horizontal or vertical
        :return: True or False whether the point is in the array
        """
        for pt in arr:
            if pt[key][0] - 15 <= point[0] <= pt[key][0] + 15 or pt[key][1] - 15 <= point[1] <= pt[key][1] + 15:
                return False
        return True

    def split_lines(self, x_values, y_values):
        """
        Function splits detected lines into horizontal and vertical lines.

        :param x_values: tuples of X values
        :param y_values: tuples of Y values
        :return: hor_lines, ver_lines: horizontal and vertical lines
        """
        hor_lines, ver_lines = [], []

        for x, y in zip(x_values, y_values):
            if x is None or y is None:
                continue

            # horizontal lines
            if abs(x[0] - x[1]) == 600:
                if self.check_line_existence(hor_lines, y, 1):
                    hor_lines.append([x, y])
                continue

            # vertical lines
            if abs(y[0] - y[1]) == 600:
                if abs(x[0] - x[1]) > 300:
                    continue

                if self.check_line_existence(ver_lines, x, 0):
                    ver_lines.append([x, y])

        return hor_lines, ver_lines

    @staticmethod
    def calculate_distance(x1, x2, y1, y2):
        """
        Helper function to calculate distance of two points.

        :param x1: x1
        :param x2: x2
        :param y1: y1
        :param y2: y2
        :return: distance
        """
        return math.sqrt(((x2 - x1) ** 2) + ((y2 - y1) ** 2))

    def remove_lines(self, lines):
        """
        Helper function to filter lines. Functions calculates distance and chooses lines with similar distance.

        :param lines: lines
        :return: corrected lines
        """
        if len(lines) < 2:
            return []

        x1, y1 = lines[0]
        x2, y2 = lines[1]
        distance = self.calculate_distance((x1[0] + x1[1]) / 2, (x2[0] + x2[1]) / 2,
                                           (y1[0] + y1[1]) / 2, (y2[0] + y2[1]) / 2)

        new_lines = [lines[0], lines[1]]
        for i in range(2, len(lines)):
            x, y = lines[i]
            dist = self.calculate_distance((x[0] + x[1]) / 2, (x2[0] + x2[1]) / 2,
                                           (y[0] + y[1]) / 2, (y2[0] + y2[1]) / 2)
            if distance - 15 <= dist <= distance + 15:
                new_lines.append(lines[i])
                x2, y2 = x, y
                distance = dist

            if len(new_lines) == 4:
                break

        return new_lines

    def filter_lines(self, lines):
        """
        Function removes lines by their distance. Keeps the same or similarly distant lines to max count of 9.
        It's split into two halves because lines in the top half of the image are closer to each other and lines on the
        bottom of the image are farther apart.

        :param lines: lines
        :return: corrected lines
        """
        distances = []

        # fine line near the middle
        for x, y in lines:
            distances.append(self.calculate_distance((abs(x[0] + x[1]) / 2), self.middle_point[0],
                                                     (abs(y[0] + y[1]) / 2), self.middle_point[1]))

        mid = min(distances)
        idx = distances.index(mid)

        # split lines into bottom and top lines
        bot_lines = lines[idx:]
        top_lines = lines[:idx + 1]

        # reverse lines to go from middle one to top edge
        top_lines = top_lines[::-1]

        # choose good lines
        top_lines = self.remove_lines(top_lines)
        bot_lines = self.remove_lines(bot_lines)

        return top_lines[1:][::-1] + bot_lines

    @staticmethod
    def sort_lines(lines, key):
        """
        Helper function for sorting lines.

        :param lines: lines
        :param key: indication of horizontal and vertical lines
        :return: sorted lines
        """
        return sorted(lines, key=lambda x: x[key])

    def check_lines_distance(self, arr):
        """
        Helper function that checks line distances.

        :param arr: lines
        :return: True or False based on correct distances.
        """

        distances = []

        for i in range(len(arr) - 1):
            x1, y1 = arr[i]
            x2, y2 = arr[i + 1]

            distances.append(self.calculate_distance((x1[0] + x1[1]) / 2, (x2[0] + x2[1]) / 2,
                                                     (y1[0] + y1[1]) / 2, (y2[0] + y2[1]) / 2))

        prev_distance = distances[0]

        for i in range(1, len(distances)):
            if not prev_distance - 20 <= distances[i] <= prev_distance + 20:
                return False

            prev_distance = distances[i]

        return True

    @staticmethod
    def det(x1, y1, x2, y2):
        """
        Helper function for line intersection.
        """
        return x1 * y2 - y1 * x2

    def calculate_lines_intersects(self, ver_lines, hor_lines):
        """
        Functions calculates intersections of lines.

        :param ver_lines: vertical lines
        :param hor_lines: horizontal lines
        :return: intersects of lines
        """
        intersects = []

        for hor_line in hor_lines:
            points = []
            for ver_line in ver_lines:
                x_diff = [hor_line[0][0] - hor_line[0][1], ver_line[0][0] - ver_line[0][1]]
                y_diff = [hor_line[1][0] - hor_line[1][1], ver_line[1][0] - ver_line[1][1]]

                div = self.det(x_diff[0], x_diff[1], y_diff[0], y_diff[1])

                # dont intersect
                if div == 0:
                    continue

                d = [self.det(hor_line[0][0], hor_line[1][0], hor_line[0][1], hor_line[1][1]),
                     self.det(ver_line[0][0], ver_line[1][0], ver_line[0][1], ver_line[1][1])]

                x = self.det(d[0], d[1], x_diff[0], x_diff[1]) / div
                y = self.det(d[0], d[1], y_diff[0], y_diff[1]) / div

                points.append([int(x), int(y)])

            intersects.append(points)

        return intersects

    def side_points(self, intersects):
        """
        Helper function to add side points to intersects.

        :param intersects: intersects
        :return: intersects appended by side points
        """
        for i, line in enumerate(intersects):
            p0_x, p0_y = line[0]
            p1_x, p1_y = line[1]

            dist = self.calculate_distance(p1_x, p0_x, p1_y, p0_y)

            new_point = [int(p0_x - dist), int(p0_y)]

            line.insert(0, new_point)

            p0_x, p0_y = line[-1]
            p1_x, p1_y = line[-2]

            dist = self.calculate_distance(p1_x, p0_x, p1_y, p0_y)

            new_point = [int(p0_x + dist), int(p0_y)]

            line.append(new_point)

            intersects[i] = line

        return intersects

    def top_and_bottom_points(self, intersects):
        """
        Helper function to add top and bottom points to intersects.

        :param intersects: intersects
        :return: intersects appended by top and bottom points
        """
        first_line, sec_line = intersects[0], intersects[1]
        last_line, penul_line = intersects[-1], intersects[-2]

        new_line1, new_line2 = [], []

        for i in range(len(intersects[0])):
            p0_x, p0_y = first_line[i]
            p1_x, p1_y = sec_line[i]

            dist = self.calculate_distance(p0_x, p1_x, p0_y, p1_y)

            try:
                slope = (p1_y - p0_y) / (p1_x - p0_x)
            except ZeroDivisionError:
                slope = (p1_y - p0_y)

            b = p0_y - (slope * p0_x)

            if slope == 0:
                slope = 1

            new_point = [(p0_y - dist - b) / slope, p0_y - dist]

            new_line1.append(new_point)

            p0_x, p0_y = last_line[i]
            p1_x, p1_y = penul_line[i]

            dist = self.calculate_distance(p0_x, p1_x, p0_y, p1_y)

            try:
                slope = (p1_y - p0_y) / (p1_x - p0_x)
            except ZeroDivisionError:
                slope = (p1_y - p0_y)

            b = p0_y - (slope * p0_x)

            if slope == 0:
                slope = 1

            new_point = [(p0_y + dist - b) / slope, p0_y + dist]

            new_line2.append(new_point)

        intersects.insert(0, new_line1)
        intersects.append(new_line2)

        return intersects

    def calculate_corners(self, intersects):
        """
        Function to calculate all corners.

        :param intersects: intersects
        :return: intersects
        """
        intersects = self.side_points(intersects)
        intersects = self.top_and_bottom_points(intersects)

        return intersects

    @staticmethod
    def calculate_center_positions(intersects):
        """
        Function calculate center points of each square.

        :param intersects: intersects
        :return: center points
        """
        center_points = []

        for i in range(len(intersects) - 1):
            first_line = intersects[i]
            sec_line = intersects[i + 1]
            points = []

            for j in range(len(first_line) - 1):
                p0_x, p0_y = first_line[j]
                p1_x, p1_y = sec_line[j + 1]

                points.append([(p1_x + p0_x) / 2, (p1_y + p0_y) / 2])

            center_points.append(points)

        return center_points

    def set_middle_point(self, event, x, y, *args):
        """
        Functions saves user input.
        Saves position of the point in the middle of the chessboard.

        :param event: mouse click
        :param x: x value
        :param y: y value
        """
        if event == cv.EVENT_LBUTTONDOWN:
            self.pressed = True
            self.middle_point = [x, y]

    def remove_excess_lines(self, lines):
        """
        Function removes excess lines if their count is bigger than 7.
        This function is called only when there is more horizontal than vertical lines and vice versa.

        :param lines: lines
        :return: lines
        """
        if len(lines) == 7:
            return lines

        # number of lines to be removed
        num_lines = len(lines) - 7

        # calculate distances from middle point
        distances = []

        for x, y in lines:
            distances.append(self.calculate_distance((abs(x[0] + x[1]) / 2), self.middle_point[0],
                                                     (abs(y[0] + y[1]) / 2), self.middle_point[1]))

        # remove the farthest lines
        for i in range(num_lines):
            max_dist = max(distances)
            idx = distances.index(max_dist)
            distances.pop(idx)
            lines.pop(idx)

        return lines

    def detect_chessboard(self, path, evaluation=False):
        """
        Main program function that manages program run.

        :param path: image path
        :param evaluation: for evaluation returns corner points
        :return: center_points
        """
        # image path
        self.path = path

        # detect lines
        x_values, y_values = self.detect_lines(path)

        # split lines into horizontal and vertical lines
        hor_lines, ver_lines = self.split_lines(x_values, y_values)

        # not enough lines detected
        if len(hor_lines) < 7 or len(ver_lines) < 7:
            print("wrong number of lines detected (1)")
            self.pressed = False
            self.middle_point = []
            return -1

        # sort lines top to bottom, left to right
        hor_lines = self.sort_lines(hor_lines, 1)
        ver_lines = self.sort_lines(ver_lines, 0)

        # for automatic evaluation
        if not evaluation:

            # setup window for middle point selection
            cv.namedWindow("img")
            cv.moveWindow("img", 100, 100)
            cv.setMouseCallback("img", self.set_middle_point)

            # middle point selection
            img = cv.imread(path)
            img = cv.resize(img, (600, 600))

            print("click in the middle of the chessboard")

            while not self.pressed:
                cv.imshow("img", img)

                k = cv.waitKey(1) & 0xFF

                if k == ord('q'):
                    exit(0)

            cv.destroyWindow("img")

        # remove bad lines
        hor_lines = self.filter_lines(hor_lines)
        ver_lines = self.filter_lines(ver_lines)

        # check that there is enough lines
        if len(ver_lines) < 7 or len(hor_lines) < 7:
            print("wrong number of lines detected (2)")
            self.pressed = False
            self.middle_point = []
            return -1

        # sort lines again to make sure of their position after filtering
        # key 1 indicates horizontal line and that y axis matters
        # key 0 indicates vertical line and that x axis matters
        hor_lines = self.sort_lines(hor_lines, 1)
        ver_lines = self.sort_lines(ver_lines, 0)

        # if there is more horizontal or vertical lines than 7 and different from 9
        # (full chessboard wasn't detected) they have to be same size
        if len(hor_lines) > 7 or len(ver_lines) > 7:
            if len(hor_lines) != 9 or len(ver_lines) != 9:
                hor_lines = self.remove_excess_lines(hor_lines)
                ver_lines = self.remove_excess_lines(ver_lines)

        # calculate intersects of vertical and horizontal lines
        intersects = self.calculate_lines_intersects(ver_lines, hor_lines)

        # calculate rest of the corners if full chessboard wasn't detected
        if len(ver_lines) == 7 and len(hor_lines) == 7:
            intersects = self.calculate_corners(intersects)

        # calculate center positions of squares
        center_points = self.calculate_center_positions(intersects)

        # for running in loop
        self.pressed = False
        self.middle_point = []

        # for automatic evaluation of chessboard detection
        if evaluation:
            return center_points, [intersects[0][0], intersects[0][-1], intersects[-1][-1], intersects[-1][0]]

        return center_points

    def display_center_points(self, center_points):
        """
        Helper function to display center points.

        :param center_points: center points
        """
        img = cv.imread(self.path)
        img = cv.resize(img, (600, 600))

        for point in center_points:
            for x, y in point:
                img = cv.circle(img, (int(x), int(y)), 3, (0, 0, 255), -1)

        print("press any button to continue")
        cv.imshow("square points", img)
        cv.waitKey(0)
        cv.destroyAllWindows()


if __name__ == '__main__':
    image = r''

    if len(sys.argv) == 2:
        image = sys.argv[1]

    if not image:
        print("Enter image path.")
        exit(1)

    bd = ChessBoardDetection()

    centerPoints = bd.detect_chessboard(image)
    if centerPoints == -1:
        exit(1)
    bd.display_center_points(centerPoints)
