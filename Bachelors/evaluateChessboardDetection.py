#!/usr/bin/env python
"""
Program evaluates chessboard detection. Used for experiments with parameters.

author : xhampl10 (xhampl10@stud.fit.vutbr.cz)
"""
import os
import math
import time
import sys

import chessboardDetectionHoughlines as chDHl

# image counter
img_counter = 0

# chessboard detected counter
chessboard_correct = 0
chessboard_incorrect = 0

# detection failed to find chessboard counter
detection_error = 0


def load_images(path):
    """
    Function loads images from directory.

    :param path: path to directory
    :return: list of images
    """
    images = []

    if not os.path.isdir(path):
        print("path is not directory")
        return images

    for file in os.listdir(path):
        if file.endswith('.jpg') or file.endswith('.jpeg') or file.endswith('.JPEG') or file.endswith('.png'):
            images.append(os.path.join(path, file))

    return images


def load_board(image_path):
    """
    Function loads coordinates of board corners.

    :param image_path: path to specific image
    :return: corner points
    """
    file_path = image_path.split('.')[0] + '_board.txt'

    if not os.path.isfile(file_path):
        print('board annotation doesnt exist for', image_path)
        return []

    points = []

    with open(file_path, 'r') as f:
        for line in f.readlines():
            points.append(list(map(int, line.rstrip().split(','))))
        f.close()

    return points


def load_middle_point(image_path):
    """
    Function loads middle point of chesbsoard.

    :param image_path: path to specific image
    :return: coordinates of point
    """
    file_path = image_path.split('.')[0] + '_midpoint.txt'

    if not os.path.isfile(file_path):
        print('middle point annotation doesnt exist for', image_path)
        return []

    with open(file_path, 'r') as f:
        for line in f.readlines():
            point = list(map(int, line.rstrip().split(',')))
        f.close()

    return point


def calculate_distance(x1, y1, x2, y2):
    """
    Function calculates distance between two points.

    :param x1:
    :param y1:
    :param x2:
    :param y2:
    :return: distance
    """
    return math.sqrt(((x2 - x1) ** 2) + ((y2 - y1) ** 2))


# chessboard evaluation
def evaluate_chessboard(board_points, detected_points):
    """
    Function evaluates board detection.

    :param board_points: test corners
    :param detected_points: detected corners
    :return: True if correct else False
    """
    global img_counter, chessboard_correct, chessboard_incorrect

    img_counter += 1

    if detected_points == -1:
        chessboard_incorrect += 1
        return

    check = True

    for i in range(4):
        dist = calculate_distance(board_points[i][0], board_points[i][1],
                                  detected_points[i][0], detected_points[i][1])

        if dist > 40:
            check = False

    if check:
        chessboard_correct += 1
    else:
        chessboard_incorrect += 1


if __name__ == "__main__":
    directory = r""

    if len(sys.argv) == 2:
        directory = sys.argv[1]

    if not directory:
        print("Enter directory.")
        exit(1)

    #chb_det = chDPC.ChessBoardDetection(d=128, t=0.35)
    chb_det = chDHl.ChessBoardDetection(threshold=50)

    images = load_images(directory)
    if not images:
        print('error in images loading')
        exit(1)

    times = []
    lines = []

    for image in images:

        mid_point = load_middle_point(image)

        if not mid_point:
            print("image doesnt have middle point", image)
            break

        board = load_board(image)

        if not board:
            print("image doesnt have board corners", image)
            break

        # chessboard detection
        chb_det.middle_point = mid_point
        chb_det.pressed = True
        startTime = time.time()
        try:
            center_points, corners = chb_det.detect_chessboard(image, evaluation=True)
        except TypeError:
            img_counter += 1
            detection_error += 1
        else:
            endTime = time.time()
            times.append(endTime - startTime)
            evaluate_chessboard(board, corners)

    print("image counter:", img_counter)
    print("avg time:", sum(times) / len(times))
    print("detection error counter:", detection_error)
    print("chessboard correct counter:", chessboard_correct)
    print("chessboard incorrect counter:", chessboard_incorrect)
