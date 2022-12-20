#!/usr/bin/env python
"""
Script inits convolutional network, passes to its image and locates them on the detected chessboard.

This program is inspired by "YOLO object detection using Opencv with Python" from Sergio canu at
https://pysource.com/2019/06/27/yolo-object-detection-using-opencv-with-python/.

author : Tomáš Hampl (xhampl10@stud.fit.vutbr.cz)
"""

import cv2 as cv
import numpy as np
import chessboardDetectionPClines as Cbd
import math
import sys


class FigureDetection:
    """
    Classes to encapsulate figure detection.
    """
    def __init__(self, net_cfg=r'./yolov4.cfg', net_weights=r'./yolov4.weights'):
        """
        Functions inits CNN.

        :param net_cfg: configuration file of CNN
        :param net_weights: weights file of CNN
        """
        self.net = cv.dnn.readNet(net_cfg, net_weights)

        layer_names = self.net.getLayerNames()
        self.output_layers = [layer_names[i[0] - 1] for i in self.net.getUnconnectedOutLayers()]


        self.chessboard = []

        self.classes = [
            "P", "B", "N", "R", "Q", "K",
            "p", "b", "n", "r", "q", "k"
        ]

    @staticmethod
    def load_image(path):
        """
        Function loads image and resizes it.

        :param path: image path
        :return: loaded and resized image
        """
        img = cv.imread(path)
        img = cv.resize(img, (600, 600))

        return img

    @staticmethod
    def calculate_distance( x1, y1, x2, y2):
        """
        Function calculates distance between two points.

        :param x1:
        :param y1:
        :param x2:
        :param y2:
        :return: distance
        """
        return math.sqrt(((x2 - x1) ** 2) + ((y2 - y1) ** 2))

    def set_chessboard(self):
        """
        Function clears chessboard for localization.
        """
        self.chessboard = [
            ["_", "_", "_", "_", "_", "_", "_", "_"],
            ["_", "_", "_", "_", "_", "_", "_", "_"],
            ["_", "_", "_", "_", "_", "_", "_", "_"],
            ["_", "_", "_", "_", "_", "_", "_", "_"],
            ["_", "_", "_", "_", "_", "_", "_", "_"],
            ["_", "_", "_", "_", "_", "_", "_", "_"],
            ["_", "_", "_", "_", "_", "_", "_", "_"],
            ["_", "_", "_", "_", "_", "_", "_", "_"]
        ]

    def create_FEN(self, chessboard):
        """
        Function creates FEN string from chessboard.

        :param chessboard: representation of chessboard in matrix.
        :return: FEN string
        """

        res = ''
        for line in chessboard:
            cnt = 0
            for i, box in enumerate(line):
                if box == "_":
                    cnt += 1
                    if i == len(line) - 1:
                        res += str(cnt)
                else:
                    if cnt != 0:
                        res += str(cnt)
                        cnt = 0

                    if box in self.classes:
                        res += box
                    else:
                        return -1

            res += "/"

        return res[:-1]

    def detect_figures(self, img_path, chessboard_center_points):
        """
        Main program function that runs detection.

        :param img_path: path to image
        :param chessboard_center_points: detected center points of squares on the chessboard
        :return: detected FEN string
        """

        img = self.load_image(img_path)

        # set clear chessboard
        self.set_chessboard()

        height, width = img.shape[:2]

        # prepare image for insertion to CNN
        blob = cv.dnn.blobFromImage(img, 0.00392, (416, 416), (0, 0, 0), True, crop=False)

        # insert image
        self.net.setInput(blob)

        # catch output
        outs = self.net.forward(self.output_layers)

        class_ids = []
        confidences = []
        boxes = []
        cords = []

        # for every detection
        for out in outs:
            for detection in out:
                scores = detection[5:]
                class_id = np.argmax(scores)
                confidence = scores[class_id]

                # threshold
                if confidence > 0.5:
                    # center position of bbox
                    center_x = int(detection[0] * width)
                    center_y = int(detection[1] * height)

                    # cords for rectangle drawing
                    w = int(detection[2] * width)
                    h = int(detection[3] * height)
                    x = int(center_x - w / 2)
                    y = int(center_y - h / 2)

                    # for localization
                    cords.append([center_x, center_y, w, h])

                    # bboxes
                    boxes.append([x, y, w, h])
                    confidences.append(float(confidence))
                    class_ids.append(class_id)

        # box suppression
        indexes = cv.dnn.NMSBoxes(boxes, confidences, 0.5, 0.4)

        for i in range(len(cords)):
            if i in indexes:
                # point
                x, y, w, h = cords[i]
                yy = y + h / 3

                b_x, b_y, b_w, b_h = boxes[i]

                cv.rectangle(img, (b_x, b_y), (b_x + b_w, b_y + b_h), (0, 0, 255), 2)

                # localization
                min_dist = 999
                index = [0, 0]

                for j, line in enumerate(chessboard_center_points):
                    for k, point in enumerate(line):
                        dist = self.calculate_distance(x, yy, point[0], point[1])

                        # save position with lowest distance ? cache it ?
                        if dist <= min_dist:
                            index = [j, k]
                            min_dist = dist

                # load position to chessboard with class id
                self.chessboard[index[0]][index[1]] = self.classes[class_ids[i]]

        cv.imshow("img", img)
        cv.waitKey(0)

        # return FEN
        return self.create_FEN(self.chessboard)


if __name__ == "__main__":
    image = r'C:\Users\Nename\Desktop\sss.png'
    net_cfg = r'C:\Users\Nename\Desktop\anotace bp\CNN\yolov4.cfg'
    net_weights = r'C:\Users\Nename\Desktop\anotace bp\CNN\yolov4.weights'

    if len(sys.argv) > 1:
        image = sys.argv[1]
        if len(sys.argv) > 2:
            net_cfg = sys.argv[2]
        if len(sys.argv) > 3:
            net_weights = sys.argv[3]

    if not image:
        print("Enter image first.")
        exit(1)

    bd = Cbd.ChessBoardDetection()
    centerPoints = bd.detect_chessboard(image)

    if centerPoints == -1:
        print("board detection failed")
        exit(1)

    fd = FigureDetection(net_cfg=net_cfg, net_weights=net_weights)
    fen = fd.detect_figures(image, chessboard_center_points=centerPoints)
    print(fen)
