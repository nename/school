#!/bin/usr/env python
"""
Helper module for board annotation.

Author: Tomáš Hampl xhampl10 (xhampl10@stud.fit.vutbr.cz)
"""


from cv2 import cv2 as cv
import os
import sys


class BoardAnnotation:
    """
    Class encapsulates board annotation module.
    """
    def __init__(self):
        """
        Init function.
        """
        self.pressed = False    # mouse press
        self.src_points = []    # board corners in image
        self.path = ""
        self.images = []
        self.img_size = 600     # image size after resize
        self.img = 0

    def load_images(self):
        """
        Function loads images from path.

        :return: True or False based on if path is good
        """
        if not os.path.isdir(self.path):
            return False

        self.images = []
        for file in os.listdir(self.path):
            # saves absolute path of each image in directory
            if file.endswith('.jpg') or file.endswith('.JPEG') or file.endswith('.png') or file.endswith('.jpeg'):
                self.images.append(os.path.join(self.path, file))
        return True

    def draw_point(self, event, x, y, *args):
        """
        Function draws point on clicked position and saves its position.

        :param event: event (click in image)
        :param x: x position in image window
        :param y: y position in image window
        """
        if event == cv.EVENT_LBUTTONDOWN:
            self.pressed = True
            self.src_points.append([x, y])
            cv.circle(self.img, (x, y), 7, (0, 0, 255), -1)
        elif event == cv.EVENT_LBUTTONUP:
            self.pressed = False

    def write_points(self, path):
        """
        Function writes coordinates into file for image.

        :param path: complete path of image
        """
        path = path.split('.')[0] + '_board.txt'

        file = open(path, 'w+')
        res = ''
        for pair in self.src_points:
            res += str(pair[0]) + ',' + str(pair[1]) + '\n'
        file.write(res)
        file.close()

    @staticmethod
    def file_exist_check(path):
        """
        Function checks whether file already exists.

        :param path: path of the file
        :return:
        """
        path = path.split('.')[0] + '_board.txt'

        if os.path.isfile(path):
            return True

        return False

    def select_points(self, path):
        """
        Function that controls point drawing and file writing.

        :param path: path to the image
        """
        self.img = cv.imread(path)
        self.img = cv.resize(self.img, (self.img_size, self.img_size))
        
        while 1:
            cv.imshow('img', self.img)

            k = cv.waitKey(1) & 0xFF

            # next image, write points, clear list of saved points
            if k == ord('n'):
                self.write_points(path)
                self.src_points = []
                break
            elif k == ord('q'):
                exit(0)

    def create_annotation(self, path):
        """
        Main program function.
        Sets up mouse callback to draw point and loads images one by one to select_points function.

        :return:
        """
        self.path = path

        if not self.load_images():
            print('bad path')
            exit(1)

        cv.namedWindow('img')
        cv.moveWindow('img', 80, 80)
        cv.setMouseCallback('img', self.draw_point)

        print('start top left corner and go clockwise and then press n for next image')

        for image in self.images:
            if self.file_exist_check(image):
                continue

            self.select_points(image)


if __name__ == "__main__":
    directory = r''

    if len(sys.argv) == 2:
        directory = sys.argv[1]

    if not directory:
        print("Enter directory first.")
        exit(1)

    b = BoardAnnotation()
    b.create_annotation(directory)
