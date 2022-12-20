#!/usr/bin/env python
"""
Module annotates middle of chessboard and saves coordinates into file.
"""
import cv2 as cv
import os
import sys


class MiddlePointAnnotation:
    """
    Class encapsulates program for middle of the chessboard annotation.
    """
    def __init__(self):
        """
        Init function.
        """
        self.images = []
        self.pressed = False
        self.middle_point = []

    def load_images(self, path):
        """
        Function loads images.

        :param path: path to directory with images
        :return: True if loaded correctly else False
        """
        if not os.path.isdir(path):
            print("path is not directory")
            return False

        for file in os.listdir(path):
            if file.endswith('.jpg') or file.endswith('.jpeg') or file.endswith('.JPEG') or file.endswith('.png'):
                self.images.append(os.path.join(path, file))

        return True

    def save_point(self, event, x, y, *args):
        """
        Even function saves coordinates in image.

        :param event: mouse click
        :param x: x coordinate
        :param y: y coordinate
        :param args:
        """
        if event == cv.EVENT_LBUTTONDOWN:
            self.pressed = True
            self.middle_point = [x, y]
        elif event == cv.EVENT_LBUTTONUP:
            self.pressed = False

    def write_point(self, path):
        """
        Function writes mid point coordinates of chessboard into image specific file.

        :param path: image path
        """
        file_path = path.split('.')[0] + '_midpoint.txt'

        file = open(file_path, 'w+')
        res = str(self.middle_point[0]) + ',' + str(self.middle_point[1]) + '\n'
        file.write(res)
        file.close()

    @staticmethod
    def file_exist(path):
        """
        Function checks for file existence.

        :param path: image path
        :return: True if exists else False
        """
        file_path = path.split('.')[0] + '_midpoint.txt'

        if os.path.isfile(file_path):
            return True

        return False

    def select_point(self, path):
        """
        Function controls point annotation.

        :param path: image path
        """
        img = cv.imread(path)
        img = cv.resize(img, (600, 600))

        while not self.pressed:
            cv.imshow("img", img)
            cv.waitKey(1)

        self.pressed = False
        self.write_point(path)

    def create_annotation(self, path):
        """
        Main program function that controls run of the program.

        :param path: image path
        """
        if not self.load_images(path):
            print("images failed to load")
            exit(1)

        cv.namedWindow('img')
        cv.moveWindow('img', 400, 400)
        cv.setMouseCallback('img', self.save_point)

        for image in self.images:
            if self.file_exist(image):
                continue

            self.select_point(image)

        cv.destroyAllWindows()


if __name__ == "__main__":
    directory = r''

    if len(sys.argv) == 2:
        directory = sys.argv[1]

    if not directory:
        print("Enter directory first.")
        exit(1)

    mpa = MiddlePointAnnotation()
    mpa.create_annotation(directory)
