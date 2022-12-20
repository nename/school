#!/usr/bin/env python
"""
Module for creation of bounding boxes.

author : Tomáš Hampl (xhampl10@stud.fit.vutbr.cz)
"""

import os
from cv2 import cv2 as cv
import numpy as np
import sys


class BoundingBoxes:
    """
    Class encapsulates creation of bounding boxes.
    """
    def __init__(self):
        """
        Init function.
        """
        self.path = ""   # directory path
        self.dst_points = [[19, 19], [581, 19], [581, 579], [19, 579]]  # destination points for homography
        self.square = 70    # square size
        self.homography = 0
        self.inverse_homography = 0
        self.positions = []     # figure positions
        self.chess_board = []   # board corners
        self.img_size = 600
        self.images = []
        self.cords = []     # positions of figures on chessboard
        self.figures = []   # figure classes
        self.sizes = [0.15, 0.45, 0.45, 0.35, 0.6, 0.8]   # default sizes of figures
        self.camera_mtx = []    # camera matrix
        self.dist = []  # distortion coefficients

    def load_images(self):
        """
        Functions loads images from directory.

        :return: True or False if load was successful
        """
        if not os.path.isdir(self.path):
            return False

        self.images = []
        for file in os.listdir(self.path):
            if file.endswith('.jpg') or file.endswith('.JPEG') or file.endswith('.png'):
                self.images.append(os.path.join(self.path, file))

        return True

    def load_positions(self, path):
        """
        Function loads figure positions.

        :param path: image path
        :return: True or False if load was successful
        """
        path_pos = path.split('.')[0] + '_pos.txt'

        self.positions = []
        file = open(path_pos, 'r')

        for line in file.readlines():
            line = line.rstrip('\n')
            self.positions.append(list(map(int, line.split(','))))

        file.close()
        return True

    def load_board(self, path):
        """
        Function loads board corners.

        :param path: image path
        :return: True or False if load was successful
        """
        path_board = path.split('.')[0] + '_board.txt'

        self.chess_board = []
        file = open(path_board, 'r')

        for line in file.readlines():
            line = line.rstrip('\n')
            x, y = line.split(',')
            self.chess_board.append([int(x), int(y)])

        file.close()
        return True

    def calculate_homography(self):
        """
        Function calculates homnography and inverse homography.
        """
        src_points = np.array(self.chess_board).reshape(-1, 1, 2)
        dst_points = np.array(self.dst_points).reshape(-1, 1, 2)

        self.homography, mask = cv.findHomography(src_points, dst_points)
        self.inverse_homography = np.linalg.inv(np.array(self.homography))

    def calibrate_camera(self):
        """
        Function calculates camera matrix and distortion coefficients.
        """
        # take first corner
        x, y = self.dst_points[0]

        points = []

        # 8 x 8 squares (9 x 9 points)
        for i in range(9):
            for j in range(9):
                # actual corner of square
                x, y = x + (i * self.square), y + (j * self.square)

                # calculate real position of points
                rp = np.dot(self.inverse_homography, np.array([x, y, 1]))

                points.append([rp[0] / rp[2], rp[1] / rp[2]])

        # corners
        points = np.array([points], np.float32)

        # object points
        obj_points = np.zeros((9 * 9, 3), np.float32)
        obj_points[:, :2] = np.mgrid[0:9, 0:9].T.reshape(-1, 2)
        obj_points = np.array([obj_points], np.float32)

        # calculate camera matrix and distortion coefficients
        ret, self.camera_mtx, self.dist, rot_vectors, trans_vectors = cv.calibrateCamera(obj_points, points,
                                                                                         (600, 600), None, None)

    def bounding_box_size(self, figure):
        """
        Helper function for figure sizes.

        :param figure: figure class
        :return: figure size
        """
        return {
            0: self.sizes[0],
            1: self.sizes[1],
            2: self.sizes[2],
            3: self.sizes[3],
            4: self.sizes[4],
            5: self.sizes[5],
            6: self.sizes[0],
            7: self.sizes[1],
            8: self.sizes[2],
            9: self.sizes[3],
            10: self.sizes[4],
            11: self.sizes[5]
        }[figure]

    def calculate_cords(self, x, y, figure):
        """
        Function calculates coordinates from image plane into real image through homography and solvesPNP and projects
        points to create bounding rectangle.

        :param x: x position on board
        :param y: y position on board
        :param figure: figure class
        :return: bounding rectangle
        """
        # coordinates of square in destination image
        tl_x = self.dst_points[0][0] + (x * self.square)
        tl_y = self.dst_points[0][1] + (y * self.square)
        tr_x = tl_x + self.square
        tr_y = tl_y
        dl_x = tl_x
        dl_y = tl_y + self.square
        dr_x = tl_x + self.square
        dr_y = tl_y + self.square

        # calculating real coordinates of square
        real_tl = np.dot(self.inverse_homography, np.array([tl_x, tl_y, 1]))
        real_tr = np.dot(self.inverse_homography, np.array([tr_x, tr_y, 1]))
        real_dl = np.dot(self.inverse_homography, np.array([dl_x, dl_y, 1]))
        real_dr = np.dot(self.inverse_homography, np.array([dr_x, dr_y, 1]))

        real_tl = [real_tl[0] / real_tl[2], real_tl[1] / real_tl[2]]
        real_tr = [real_tr[0] / real_tr[2], real_tr[1] / real_tr[2]]
        real_dl = [real_dl[0] / real_dl[2], real_dl[1] / real_dl[2]]
        real_dr = [real_dr[0] / real_dr[2], real_dr[1] / real_dr[2]]

        # real square points
        image_points = np.array([[real_tl, real_tr, real_dl, real_dr]], np.float32)

        # object points
        obj_points = np.zeros((2 * 2, 3), np.float32)
        obj_points[:, :2] = np.mgrid[0:2, 0:2].T.reshape(-1, 2)
        obj_points = np.array([obj_points], np.float32)

        # figure size
        size = self.bounding_box_size(figure)

        # axis to draw box around square
        axis = np.float32([[0, 0, 0], [0, 1, 0], [1, 1, 0], [1, 0, 0], [0, 0, -1 * size],
                           [0, 1, -1 * size], [1, 1, -1 * size], [1, 0, -1 * size]])

        # solve PnP
        ret, rot_vectors, trans_vectors = cv.solvePnP(obj_points, image_points, self.camera_mtx, self.dist)

        # project points into box around square
        img_pts, jac = cv.projectPoints(axis, rot_vectors, trans_vectors, self.camera_mtx, self.dist)

        # form points
        img_pts = np.int32(img_pts).reshape(-1, 2)

        return img_pts

    def locate_square(self):
        """
        Function locates square on the board.
        """
        # for each position calculate its real coordinates
        for position in self.positions:
            figure, x, y = position
            self.figures.append(figure)

            cords = self.calculate_cords(x, y, figure)

            self.cords.append(cords)

    @staticmethod
    def find_min_max_cords(cords):
        """
        Function returns min and max value of x and y. Helper function for bounding box creation.

        :param cords: rectangle coordinates
        :return: min and max values
        """
        x, y = [], []
        for pair in cords:
            x.append(pair[0])
            y.append(pair[1])

        return min(x), max(x), min(y), max(y)

    def convert_cords(self, cords):
        """
        Function converts coordinates into YOLO convention.

        :param cords: coordinates
        :return: YOLO coordinates
        """
        x_min, x_max, y_min, y_max = self.find_min_max_cords(cords)

        dw = 1. / self.img_size
        dh = 1. / self.img_size

        # center of rectangle
        x = (x_min + x_max)/2.0
        y = (y_min + y_max)/2.0

        # width and height of rectangle
        w = x_max - x_min
        h = y_max - y_min

        return [x * dw, y * dh, w * dw, h * dh]

    def write_cords(self, path):
        """
        Function writes coordinates into file.

        :param path: image path
        """
        path = path.split('.')[0] + '.txt'

        file = open(path, 'w+')

        res = ''
        for cords in self.cords:

            # convert to yolo convention
            cords = self.convert_cords(cords)

            # write class and coordinates
            res += str(self.figures[0]) + ' '

            for item in cords:
                res += str(item) + ' '

            self.figures.pop(0)
            res += '\n'

        self.cords = []
        file.write(res)
        file.close()

    # draw bounding boxes
    def draw_bounding_boxes(self, image):
        """
        Function draws bounding boxes.

        :param image: iamge path
        """

        img = cv.imread(image)
        img = cv.resize(img, (600, 600))
        cv.namedWindow('img')
        cv.moveWindow('img', 80, 80)

        for img_pts in self.cords:
            # bottom square
            cv.line(img, (img_pts[0][0], img_pts[0][1]), (img_pts[1][0], img_pts[1][1]), (0, 0, 255), 3)
            cv.line(img, (img_pts[1][0], img_pts[1][1]), (img_pts[2][0], img_pts[2][1]), (0, 0, 255), 3)
            cv.line(img, (img_pts[2][0], img_pts[2][1]), (img_pts[3][0], img_pts[3][1]), (0, 0, 255), 3)
            cv.line(img, (img_pts[3][0], img_pts[3][1]), (img_pts[0][0], img_pts[0][1]), (0, 0, 255), 3)

            # top square
            cv.line(img, (img_pts[4][0], img_pts[4][1]), (img_pts[5][0], img_pts[5][1]), (0, 0, 255), 3)
            cv.line(img, (img_pts[5][0], img_pts[5][1]), (img_pts[6][0], img_pts[6][1]), (0, 0, 255), 3)
            cv.line(img, (img_pts[6][0], img_pts[6][1]), (img_pts[7][0], img_pts[7][1]), (0, 0, 255), 3)
            cv.line(img, (img_pts[7][0], img_pts[7][1]), (img_pts[4][0], img_pts[4][1]), (0, 0, 255), 3)

            # lines connecting top and bottom
            cv.line(img, (img_pts[0][0], img_pts[0][1]), (img_pts[4][0], img_pts[4][1]), (0, 0, 255), 3)
            cv.line(img, (img_pts[1][0], img_pts[1][1]), (img_pts[5][0], img_pts[5][1]), (0, 0, 255), 3)
            cv.line(img, (img_pts[2][0], img_pts[2][1]), (img_pts[6][0], img_pts[6][1]), (0, 0, 255), 3)
            cv.line(img, (img_pts[3][0], img_pts[3][1]), (img_pts[7][0], img_pts[7][1]), (0, 0, 255), 3)

        # show image
        while 1:
            cv.imshow('img', img)

            k = cv.waitKey(1) & 0xFF

            if k == ord('n'):
                break
            elif k == ord('q'):
                exit(0)

    # helper function for file check
    @staticmethod
    def files_exist_check(path):
        """
        Helper function checks whether all necessary files exist.

        :param path: image path
        :return: True or False
        """
        path = path.split('.')[0]

        # file with board corners
        path_txt = path + '_board.txt'

        # file with figure positions
        path_pos = path + '_pos.txt'

        if not os.path.isfile(path_txt) or not os.path.isfile(path_pos):
            return False

        return True

    @staticmethod
    def file_check(path):
        """
        Helper function checks whether were bounding boxes already created for image.

        :param path: image path
        :return: True or False
        """
        path_txt = path.split('.')[0] + '.txt'

        if os.path.isfile(path_txt):
            return False
        return True

    # main function that runs program
    def create_bounding_boxes(self, path, draw_boxes=False, sizes=None):
        """
        Main program function. Function manages run of the program.

        :param path: path to directory with images
        :param draw_boxes: indicates if user want to see bounding rectangles drawn
        :param sizes: figure sizes
        """
        self.path = path

        if not self.load_images():
            print('bad path\n')
            exit(1)

        # update default sizes for figures
        if sizes is not None:
            if len(sizes) != 6:
                print("error, wrong number of figure sizes\n")
            else:
                for i, size in enumerate(sizes):
                    self.sizes[i] = size

        for image in self.images:
            # check if notation already exists
            if not self.file_check(image):
                continue

            # check corner and figure position files
            if not self.files_exist_check(image):
                print('create anotations first for image: ', image)
                break

            self.load_positions(image)
            self.load_board(image)
            self.calculate_homography()
            self.calibrate_camera()
            self.locate_square()

            if draw_boxes:
                print("press n for next image")
                self.draw_bounding_boxes(image)

            self.write_cords(image)

    def display_bounding_rectangles(self):
        """
        Function displays created bounding boxes.
        """
        if not self.load_images():
            print('bad path\n')
            exit(1)

        print("press n for next image\npress q for exit\n")

        for image in self.images:
            path = image.split('.')[0] + '.txt'

            img = cv.imread(image)
            img = cv.resize(img, (600, 600))

            # check if file exist
            if not os.path.isfile(path):
                return False

            file = open(path, 'r')
            for line in file.readlines():
                line = line.rstrip()
                
                fig, x, y, w, h = list(map(float, line.split(' ')))

                # from yolo to rectangle points
                left = int((x - w / 2) * self.img_size)
                right = int((x + w / 2) * self.img_size)
                top = int((y - h / 2) * self.img_size)
                bot = int((y + h / 2) * self.img_size)

                # boundings of image
                if left < 0:
                    left = 0
                if right > self.img_size - 1:
                    right = self.img_size - 1
                if top < 0:
                    top = 0
                if bot > self.img_size - 1:
                    bot = self.img_size - 1

                cv.rectangle(img, (left, top), (right, bot), (0, 255, 0), 2)

            file.close()

            # display
            while 1:
                cv.imshow('img', img)

                k = cv.waitKey(1) & 0xFF

                if k == ord('n'):
                    break
                elif k == ord('q'):
                    cv.destroyWindow("boxes")
                    exit(0)

        cv.destroyWindow("boxes")


if __name__ == "__main__":
    directory = r''

    if len(sys.argv) == 2:
        directory = sys.argv[1]

    if not directory:
        print("Enter directory first.")
        exit(1)

    # init
    bb = BoundingBoxes()

    # sizes = [pawn, bishop, knight, rook, queen, king]
    bb.create_bounding_boxes(directory, True, sizes=[0.15, 0.45, 0.45, 0.35, 0.6, 0.8])
    bb.display_bounding_rectangles()
