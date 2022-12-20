#!/usr/bin/env python
"""
Module detects chessboard using Hough transform.

author: xhampl10 (xhampl10@stud.fit.vutbr.cz)
"""

import numpy as np
from cv2 import cv2 as cv
import math
import sys
import statistics


class ChessBoardDetection:
    """
    Class encapsulates chessboard detection using Hough transform.
    """
    def __init__(self, threshold=60, min_line_length=200, max_line_gap=100):
        """
        Init function.

        :param threshold: detection threshold
        :param min_line_length: minimal line length
        :param max_line_gap: maximal line gap
        """
        self.img_shape = (600, 600)
        self.path = ""      # image path
        self.gap = 15       # line gap for joining lines
        self.pressed = False       # mouse press
        self.middle_point = []     # middle point of chessboard (user input)

        # HoughLinesP parameters
        self.threshold = threshold  # detection threshold
        self.min_line_length = min_line_length  # minimal line length
        self.max_line_gap = max_line_gap    # maximal line gap

    def detect_lines(self, path):
        """
        Function detects lines using HoughlinesP and returns them.

        :param path: image path
        :return: detected lines
        """

        # load image
        img = cv.imread(path, cv.IMREAD_GRAYSCALE)

        img = cv.resize(img, self.img_shape)

        # reduce noise
        img = cv.bilateralFilter(img, 3, 25, 75)

        # edges
        edges = cv.Canny(img, 150, 250)

        # line detection
        lines = cv.HoughLinesP(edges, 1, np.pi / 180, self.threshold,
                               minLineLength=self.min_line_length, maxLineGap=self.max_line_gap)

        return lines

    @staticmethod
    def check_line_existence(line, lines):
        """
        Function check whether similar line is already in the array of lines.

        :param line: line
        :param lines:  lines
        :return: True or False based on if similar line is in the array.
        """
        x1, y1, x2, y2 = line

        for x3, y3, x4, y4 in lines:
            if (x3 - 15 <= x1 <= x3 + 15 and x4 - 15 <= x2 <= x4 + 15
                    and y3 - 15 <= y1 <= y3 + 15 and y4 - 15 <= y2 <= y4 + 15):

                return False

        return True

    def split_lines(self, lines):
        """
        Function split lines by their direction to horizontal and vertical ones.

        :param lines: lines
        :return: horizontal and vertical lines
        """
        directions = []

        for line in lines:
            for x1, y1, x2, y2 in line:
                p0 = np.array([x1, y1])
                p1 = np.array([x2, y2])
                directions.append(p1 - p0)

        # normalize each direction vector to unit length
        directions = np.array(directions) / np.linalg.norm(directions, axis=1)[:, np.newaxis]

        hor, ver = [], []

        for i, line in enumerate(lines):
            if abs(directions[i][0]) > 0.80:
                if self.check_line_existence(*line, hor):
                    hor.append(list(line[0]))

            if abs(directions[i][1]) > 0.80:
                if self.check_line_existence(*line, ver):
                    ver.append(list(line[0]))

        return hor, ver

    @staticmethod
    def find_similar_lines(lines, gap, key):
        """
        Function finds similar lines.

        :param lines: lines
        :param gap: gap of the lines
        :param key: marking of horizontal or vertical line
        :return: arrays of indexes of similar lines
        """
        similar = []

        for i in range(len(lines)):
            indexes = [i]
            for j in range(len(lines)):
                if lines[i] == lines[j]:
                    continue
                if (lines[i][key]) - gap <= lines[j][key] <= (lines[i][key]) + gap and lines[i][key + 2] - gap <= \
                        lines[j][key + 2] <= lines[i][key + 2] + gap:
                    indexes.append(j)

            similar.append(indexes)

        return similar

    @staticmethod
    def check_similar_clusters(arrays):
        """
        Functions finds sub-arrays in array of similar lines.

        :param arrays: array of indexes
        :return: arrays without sub-arrays
        """
        for i in range(len(arrays)):
            if not arrays[i]:
                continue
            for j in range(len(arrays)):
                if not arrays[j]:
                    continue

                if arrays[i] == arrays[j]:
                    continue

                cnt = 0
                if len(arrays[i]) > len(arrays[j]):
                    for item in arrays[j]:
                        if item in arrays[i]:
                            cnt += 1

                    if cnt == len(arrays[j]):
                        arrays[j] = []
                else:
                    for item in arrays[i]:
                        if item in arrays[j]:
                            cnt += 1

                    if cnt == len(arrays[i]):
                        arrays[i] = []

        arrays = [item for item in arrays if item != []]

        return arrays

    @staticmethod
    def join_lines(lines, arrays):
        """
        Function joins together lines that are close to each other.

        :param lines: lines
        :param arrays: indexes of similar lines
        :return: joined lines
        """
        new_lines = []

        for item in arrays:
            x1, y1, x2, y2 = 0, 0, 0, 0
            ln = len(item)
            for index in item:
                tx1, ty1, tx2, ty2 = lines[index]

                x1 += tx1
                y1 += ty1
                x2 += tx2
                y2 += ty2

            if x1 == x2:
                if y1 > y2:
                    x1 += len(item)
                else:
                    x2 += len(item)
            if y1 == y2:
                if x1 > x2:
                    y1 += len(item)
                else:
                    y2 += len(item)

            new_lines.append([int(x1 / ln), int(y1 / ln), int(x2 / ln), int(y2 / ln)])

        return new_lines

    @staticmethod
    def calculate_slopes(lines):
        """
        Function calculates lines slopes.

        :param lines: lines
        :return: slopes
        """
        slopes = []
        for x1, y1, x2, y2 in lines:
            if x1 != x2:
                slopes.append((y2 - y1) / (x2 - x1))
            else:
                slopes.append(None)

        return slopes

    @staticmethod
    def calculate_y_intersect(lines, slopes):
        """
        Function calculates y intersect for lines.

        :param lines: lines
        :param slopes: slopes of lines
        :return: y intersects
        """
        b = []

        for i in range(len(lines)):
            x1, y1, x2, y2 = lines[i]
            slope = slopes[i]
            if slope is None:
                slope = 0

            b.append(y1 - (slope * x1))

        return b

    @staticmethod
    def extend_horizontal_lines(lines, slopes, bs):
        """
        Function extends horizontal lines.

        :param lines: horizontal lines
        :param slopes: slopes of lines
        :param bs: y intersects
        :return: extended lines
        """
        for i in range(len(lines)):
            x1, y1, x2, y2 = lines[i]
            slope = slopes[i]
            b = bs[i]

            # y = m * x + b

            if x1 > x2:
                x1 = 600
                y1 = slope * 600 + b
                x2 = 0
                y2 = slope * 0 + b
            else:
                x2 = 600
                y2 = slope * 600 + b
                x1 = 0
                y1 = slope * 0 + b

            lines[i] = [int(x1), int(y1), int(x2), int(y2)]

        return lines

    @staticmethod
    def extend_vertical_lines(lines, slopes, bs):
        """
        Functions extends vertical lines.

        :param lines: vertical lines
        :param slopes: slopes of lines
        :param bs: y intersects
        :return: extended vertical lines
        """
        for i in range(len(lines)):
            x1, y1, x2, y2 = lines[i]
            slope = slopes[i]
            b = bs[i]

            if slope is None or slope == 0:
                slope = 1

            # x = (y - b) / m

            if y1 > y2:
                try:
                    x1 = (600 - b) / slope
                    y1 = 600
                    x2 = (0 - b) / slope
                    y2 = 0
                except ZeroDivisionError:
                    x1 = (600 - b)
                    y1 = 600
                    x2 = (0 - b)
                    y2 = 0
            else:
                try:
                    x2 = (600 - b) / slope
                    y2 = 600
                    x1 = (0 - b) / slope
                    y1 = 0
                except ZeroDivisionError:
                    x2 = (600 - b)
                    y2 = 600
                    x1 = (0 - b)
                    y1 = 0

            lines[i] = [int(x1), int(y1), int(x2), int(y2)]

        return lines

    @staticmethod
    def sort_lines(lines, key):
        """
        Function sorts lines by axis.

        :param lines: lines
        :param key: axis mark
        :return: sorted lines
        """
        for i in range(len(lines)):
            x1, y1, x2, y2 = lines[i]
            if y1 > y2:
                lines[i] = [x2, y2, x1, y1]

        lines = sorted(lines, key=lambda x: x[key])
        return lines

    @staticmethod
    def det(x1, y1, x2, y2):
        """
        Helper function for calculating lines intersects.

        :param x1:
        :param y1:
        :param x2:
        :param y2:
        :return:
        """
        return x1 * y2 - y1 * x2

    def calculate_lines_intersects(self, ver_lines, hor_lines):
        """
        Functions calculates lines intersects.

        :param ver_lines: vertical lines
        :param hor_lines: horizontal lines
        :return: intersects
        """
        intersects = []

        for hor in hor_lines:
            points = []
            for ver in ver_lines:
                x_diff = [hor[0] - hor[2], ver[0] - ver[2]]
                y_diff = [hor[1] - hor[3], ver[1] - ver[3]]

                div = self.det(x_diff[0], x_diff[1], y_diff[0], y_diff[1])

                # dont intersect
                if div == 0:
                    continue

                d = [self.det(*hor), self.det(*ver)]

                x = self.det(d[0], d[1], x_diff[0], x_diff[1]) / div
                y = self.det(d[0], d[1], y_diff[0], y_diff[1]) / div

                points.append([int(x), int(y)])

            intersects.append(points)

        return intersects

    @staticmethod
    def calculate_line_distance(x1, y1, x2, y2):
        """
        Helper function to calculate line distance.

        :param x1:
        :param y1:
        :param x2:
        :param y2:
        :return:
        """
        return math.sqrt((x2 - x1) ** 2 + (y2 - y1) ** 2)

    def set_middle_point(self, event, x, y, *args):
        """
        Function for user input to mark middle of the chessboard.

        :param event: mouse click
        :param x: x value
        :param y: y value
        """
        if event == cv.EVENT_LBUTTONDOWN:
            self.pressed = True
            self.middle_point = [x, y]

    def remove_lines(self, lines):
        """
        Function removes bad lines and chooses lines in similar distance.

        :param lines: lines
        :return: selected lines
        """
        if len(lines) < 2:
            return []

        x1, y1, x2, y2 = lines[0]
        x3, y3, x4, y4 = lines[1]

        # distance of first line
        prev_dist = self.calculate_line_distance((x1 + x2) / 2, (y1 + y2) / 2, (x3 + x4) / 2, (y3 + y4) / 2)

        new_lines = [lines[0], lines[1]]

        # go through all lines and calculate their distances
        for i in range(2, len(lines)):
            x1, y1, x2, y2 = lines[i]

            dist = self.calculate_line_distance((x1 + x2) / 2, (y1 + y2) / 2, (x3 + x4) / 2, (y3 + y4) / 2)

            # if distance of previous lines is similar, append line
            if prev_dist - 10 <= dist <= prev_dist + 10:
                new_lines.append(lines[i])
                x3, y3, x4, y4 = x1, y1, x2, y2
                prev_dist = dist

            # if we have more than 5 lines in half
            if len(new_lines) == 4:
                break

        return new_lines

    def filter_lines(self, lines):
        """
        Function filters out lines by finding the middle one.

        :param lines: lines
        :return: selected lines
        """
        distances = []

        for x1, y1, x2, y2 in lines:
            distances.append(self.calculate_line_distance((abs(x1 + x2) / 2), abs(y1 + y2) / 2,
                                                          self.middle_point[0], self.middle_point[1]))

        mid = min(distances)
        idx = distances.index(mid)

        bot_lines = lines[idx:]
        top_lines = lines[:idx + 1]

        top_lines = top_lines[::-1]

        top_lines = self.remove_lines(top_lines)
        bot_lines = self.remove_lines(bot_lines)

        return top_lines[1:][::-1] + bot_lines

    def remove_excess_lines(self, lines):
        """
        Function removes excess lines to make sure there is same count of lines.

        :param lines:
        :return:
        """
        if len(lines) == 7:
            return lines

        if len(lines) < 9:
            num_lines = len(lines) - 7
        else:
            num_lines = len(lines) - 9

        distances = []

        for x1, y1, x2, y2 in lines:
            distances.append(self.calculate_line_distance((abs(x1 + x2) / 2), abs(y1 + y2) / 2,
                                                          self.middle_point[0], self.middle_point[1]))

        for i in range(num_lines):
            max_dist = max(distances)
            idx = distances.index(max_dist)
            distances.pop(idx)
            lines.pop(idx)

        return lines

    def side_points(self, intersects):
        """
        Helper function to add side points to intersects.

        :param intersects: intersects
        :return: intersects appended by side points
        """
        for i, line in enumerate(intersects):
            p0_x, p0_y = line[0]
            p1_x, p1_y = line[1]

            dist = self.calculate_line_distance(p1_x, p1_y, p0_x, p0_y)

            new_point = [int(p0_x - dist), int(p0_y)]

            line.insert(0, new_point)

            p0_x, p0_y = line[-1]
            p1_x, p1_y = line[-2]

            dist = self.calculate_line_distance(p1_x, p1_y, p0_x, p0_y)

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

            dist = self.calculate_line_distance(p0_x, p0_y, p1_x, p1_y)

            try:
                slope = (p1_y - p0_y) / (p1_x - p0_x)
            except ZeroDivisionError:
                slope = (p1_y - p0_y)

            b = p0_y - (slope * p0_x)

            new_point = [(p0_y - dist - b) / slope, p0_y - dist]

            new_line1.append(new_point)

            p0_x, p0_y = last_line[i]
            p1_x, p1_y = penul_line[i]

            dist = self.calculate_line_distance(p0_x, p0_y, p1_x, p1_y)

            try:
                slope = (p1_y - p0_y) / (p1_x - p0_x)
            except ZeroDivisionError:
                slope = (p1_y - p0_y)

            b = p0_y - (slope * p0_x)

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

    def detect_chessboard(self, path, evaluation=False):
        """
        Main program function that manages program run.

        :param path: image path
        :param evaluation: bool parameter for evaluation
        :return: center points of squares
        """
        self.path = path

        # detect lines
        lines = self.detect_lines(path)

        # split lines
        hor_lines, ver_lines = self.split_lines(lines)

        # lines werent detected
        if len(hor_lines) < 7 and len(ver_lines) < 7:
            print("wrong number of lines detected")
            return -1

        # for automatic evaluation purposes
        if not evaluation:
            # create window for middle point input
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

        # find indexes of similar lines
        hor_similar = self.find_similar_lines(hor_lines, self.gap, 1)
        ver_similar = self.find_similar_lines(ver_lines, self.gap, 0)

        # find subarrays of similar lines
        hor_similar = self.check_similar_clusters(hor_similar)
        ver_similar = self.check_similar_clusters(ver_similar)

        # join lines together
        hor_lines = self.join_lines(hor_lines, hor_similar)
        ver_lines = self.join_lines(ver_lines, ver_similar)

        # sort lines
        hor_lines = self.sort_lines(hor_lines, 1)
        ver_lines = self.sort_lines(ver_lines, 0)

        # calculate line slopes
        hor_slopes = self.calculate_slopes(hor_lines)
        ver_slopes = self.calculate_slopes(ver_lines)

        # calculate y intersect
        hor_y_intersects = self.calculate_y_intersect(hor_lines, hor_slopes)
        ver_y_intersects = self.calculate_y_intersect(ver_lines, ver_slopes)

        # extend lines
        hor_lines = self.extend_horizontal_lines(hor_lines, hor_slopes, hor_y_intersects)
        ver_lines = self.extend_vertical_lines(ver_lines, ver_slopes, ver_y_intersects)

        # sort lines
        hor_lines = self.sort_lines(hor_lines, 1)
        ver_lines = self.sort_lines(ver_lines, 0)

        # choose good lines
        hor_lines = self.filter_lines(hor_lines)
        ver_lines = self.filter_lines(ver_lines)

        if len(hor_lines) < 7 or len(ver_lines) < 7:
            print("wrong number of lines detected")
            return -1

        # sort them again to be sure of the order
        hor_lines = self.sort_lines(hor_lines, 1)
        ver_lines = self.sort_lines(ver_lines, 0)

        # remove excess lines
        if len(hor_lines) > 7 or len(ver_lines) > 7:
            if len(hor_lines) != 9 or len(ver_lines) != 9:
                hor_lines = self.remove_excess_lines(hor_lines)
                ver_lines = self.remove_excess_lines(ver_lines)

        img = cv.imread(path)
        img = cv.resize(img, (600, 600))

        for x1, y1, x2, y2 in hor_lines:
            cv.line(img, (x1, y1), (x2, y2), (0, 0, 255), 3)

        for x1, y1, x2, y2 in ver_lines:
            cv.line(img, (x1, y1), (x2, y2), (0, 0, 255), 3)

        cv.imshow("img", img)
        cv.waitKey(0)

        # line intersects
        intersects = self.calculate_lines_intersects(ver_lines, hor_lines)

        # calculate rest of the chessboard
        if len(hor_lines) == 7 and len(ver_lines) == 7:
            intersects = self.calculate_corners(intersects)

        # center point of squares
        center_points = self.calculate_center_positions(intersects)

        # reset if ran in loop
        self.pressed = False
        self.middle_point = []

        # for automatic evaluation of chessboard detection
        if evaluation:
            return center_points, [intersects[0][0], intersects[0][-1], intersects[-1][-1], intersects[-1][0]]

        return center_points


if __name__ == "__main__":
    image = r'C:\Users\Nename\Desktop\sss.png'

    if len(sys.argv) == 2:
        image = sys.argv[1]

    if not image:
        print("Enter image path.")
        exit(1)

    # init class
    bd = ChessBoardDetection()

    # detect chessboard
    centerPoints = bd.detect_chessboard(image)

    # error handling
    if centerPoints == -1:
        print("detection failed")
        exit(1)

    # display
    bd.display_center_points(centerPoints)
