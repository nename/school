#!/usr/bin/env python
"""
Helper module for annotating figures.

Author: Tomáš Hampl xhampl10@stud.fit.vutbr.cz
"""


from cv2 import cv2 as cv
import os


class FigureAnnotation:
    """
    Class encapsulates figure annotation program.
    """
    def __init__(self):
        """
        Init function.
        """
        self.pressed = False    # mouse click
        self.images = []

        self.img_size = 600     # image size (resized to 600)
        self.path = ""        # directory path

        self.dst_image = 0
        self.dst_image_path = 'dst.jpg'  # helper board for square annotation
        self.square = 70        # square size

        self.square_points = []     # saved figure class and its position
        self.figure = ''        # figure class

        self.last_pos = ''      # previous annotated position for roll

    def load_images(self):
        """
        Function load images from directory.

        :return: True or False based on good path
        """
        if not os.path.isdir(self.path):
            return False

        self.images = []
        for file in os.listdir(self.path):
            # saves absolute path of every image in directory
            if file.endswith('.jpg') or file.endswith('.JPEG') or file.endswith('.png') or file.endswith('.jpeg'):
                self.images.append(os.path.join(self.path, file))
        return True

    def find_square(self, x, y):
        """
        Function calculates position of square in image.

        :param x: x position in image
        :param y: y position in image
        :return: triplet of figure and board indexes
        """
        # destination chessboard corners 19, 19 - 581, 579
        if x in range(19, 581) and y in range(19, 579):
            # returns triplet [figure class, x board position, y board position]
            return [self.figure_class(self.figure), int((x - 19) / self.square), int((y - 19) / self.square)]
        else:
            return False

    def draw_point(self, event, x, y, *args):
        """
        Function draws point on x, y coordinates and saves location of figure.

        :param event: event (mouse click)
        :param x: x position
        :param y: y position
        """
        if event == cv.EVENT_LBUTTONDOWN:
            self.pressed = True
            tmp = False

            if not self.figure:
                self.figure = input("zadej figurku: ")
                tmp = True

            if self.figure not in ["wp", "wb", "wkn", "wr", "wq", "wk",
                                   "bp", "bb", "bkn", "br", "bq", "bk"]:
                return

            self.square_points.append(self.find_square(x, y))   # finds square position and saves it

            if self.figure[0] == "w":
                cv.circle(self.dst_image, (x, y), 5, (0, 255, 0), -1)
            else:
                cv.circle(self.dst_image, (x, y), 5, (255, 0, 0), -1)

            cv.putText(self.dst_image, self.figure, (x - 20, y - 10),
                       cv.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)

            if tmp:
                self.figure = ''
        elif event == cv.EVENT_LBUTTONUP:
            self.pressed = False

    @staticmethod
    def figure_class(figure):
        """
        Function returns class number of figure.

        :param figure: figure name
        :return: figure class number
        """
        if figure == 'wp':
            return 0
        elif figure == 'wb':
            return 1
        elif figure == 'wkn':
            return 2
        elif figure == 'wr':
            return 3
        elif figure == 'wq':
            return 4
        elif figure == 'wk':
            return 5
        elif figure == 'bp':
            return 6
        elif figure == 'bb':
            return 7
        elif figure == 'bkn':
            return 8
        elif figure == 'br':
            return 9
        elif figure == 'bq':
            return 10
        elif figure == 'bk':
            return 11
        else:
            return -1

    def write_points(self, path):
        """
        Function writes figure class and board coordinates into file.

        :param path: image path
        """
        txt_path = path.split('.')[0] + '_pos.txt'

        if os.path.isfile(txt_path):
            return

        file = open(txt_path, 'w+')
        res = ''
        for triplet in self.square_points:
            res += str(triplet[0]) + ',' + str(triplet[1]) + ',' + str(triplet[2]) + '\n'

        self.last_pos = res
        file.write(res)
        file.close()

    @staticmethod
    def file_exist_check(path):
        """
        Helper function that checks if file does exist.

        :param path: file path
        :return: True or False based on file existing
        """
        path_txt = path.split('.')[0] + '_pos.txt'

        if os.path.isfile(path_txt):
            return True

        return False

    def roll_positions(self, path):
        """
        Function rolls previous position to right / left or saves same position.

        :param path: file path
        """
        txt_path = path.split('.')[0] + '_pos.txt'

        if os.path.isfile(txt_path):
            return

        if self.last_pos == '':     # cannot roll without firstly annotating first image
            print("roll without previously annotated figures")
            return -1

        file = open(txt_path, 'w+')

        roll = input("roll left - rl, roll right - rr, same pos - s: ")

        positions = self.last_pos.rstrip().split('\n')
        res = ''

        if roll == 'rl':    # roll left for every position
            for pos in positions:
                cl, x, y = pos.split(',')   # class, x position, y position
                x, y = int(y), 7 - int(x)
                res += cl + ',' + str(x) + ',' + str(y) + '\n'

        elif roll == 'rr':  # roll right for every position
            for pos in positions:
                cl, x, y = pos.split(',')   # class, x position, y position
                x, y = 7 - int(y), int(x)
                res += cl + ',' + str(x) + ',' + str(y) + '\n'

        elif roll == 's':   # same position as previous image
            res = self.last_pos
        else:
            print("wrong input in roll")
            return -1     # wrong input

        self.last_pos = res
        file.write(res)

    def select_points(self, path):
        """
        Function that manages image displaying and main functions as write and roll.

        :param path: image path
        """
        # load destination image for annotations
        self.dst_image = cv.imread(self.dst_image_path)
        self.dst_image = cv.resize(self.dst_image, (self.img_size, self.img_size))

        while 1:
            cv.imshow('dst', self.dst_image)

            k = cv.waitKey(1) & 0xFF

            if k == ord('n'):       # n - next image
                self.write_points(path)
                self.square_points = []
                self.figure = ""
                break
            elif k == ord('r'):     # r - roll image
                if self.roll_positions(path) != -1:
                    break
            elif k == ord('f'):
                self.figure = input('zadej figurku: ')
            elif k == ord('q'):     # q - quit
                exit(0)

    def create_annotation(self, path):
        """
        Managing function that loads images into select_points function and displays images.

        :param path: path to directoryz with images
        """
        self.path = path

        if not self.load_images():
            print('bad path\n')
            exit(1)

        # creates window for square selection
        cv.namedWindow('dst')
        cv.moveWindow('dst', 80, 80)
        cv.setMouseCallback('dst', self.draw_point)

        # creates window for images to be annotated
        cv.namedWindow('src')
        cv.moveWindow('src', 700, 80)

        prev_img = None

        for image in self.images:
            if self.file_exist_check(image):
                continue

            img = cv.imread(image)
            img = cv.resize(img, (self.img_size, self.img_size))

            cv.imshow('src', img)

            if prev_img is None:
                prev_img = img
            else:
                cv.imshow("prev img", prev_img)
                prev_img = img
            cv.waitKey(1)   # show prev_img but continue program run

            self.select_points(image)


if __name__ == "__main__":
    directory = r''

    if not directory:
        print("Enter path to directory first.")
        exit(1)

    b = FigureAnnotation()
    b.create_annotation(directory)
    print('done')
