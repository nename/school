#!/usr/bin/env python
"""
Script for evaluation of localization based on chessboard and figure detection.

author : Tomáš Hampl (xhampl10@stud.fit.vutbr.cz)
"""

import os
import math
import time

import figureDetection as fD
import chessboardDetectionHoughlines as chDHl

# image counter
counter = 0

# chessboard detection counter
correct_chessboard = 0
incorrect_chessboard = 0

# localization counter
correct_localization = 0
incorrect_localization = 0

# figures counter
figure_correct = 0
figure_incorrect = 0

# complete error counter
detection_error = 0

# classes for white and black figures
classes = [
    "P", "B", "N", "R", "Q", "K",
    "p", "b", "n", "r", "q", "k"
]


def load_images(path):
    """
    Function loads all images from directory.

    :param path: path to directory
    :return: list of images
    """
    if not os.path.isdir(path):
        print("path is not directory")
        return []

    images = []

    for file in os.listdir(path):
        if file.endswith('.jpg') or file.endswith('.jpeg') or file.endswith('.JPEG') or file.endswith('.png'):
            images.append(os.path.join(path, file))

    return images


def load_board(image_path):
    """
    Function loads board corners for specific image.

    :param image_path: image path for which the data is loaded
    :return: board corner points
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


def load_positions(image_path):
    """
    Function loads position of figures for specific image.

    :param image_path: image path for which the data is loaded
    :return: list of positions
    """
    file_path = image_path.split('.')[0] + '_pos.txt'

    if not os.path.isfile(file_path):
        print('board annotation doesnt exist for', image_path)
        return []

    positions = []

    with open(file_path, 'r') as f:
        for line in f.readlines():
            positions.append(list(map(int, line.rstrip().split(','))))
        f.close()

    return positions


def load_middle_point(image_path):
    """
    Function loads coordinates of midle of the chessboard for specific image.

    :param image_path: image path for which the data is loaded
    :return: coordinates in list
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


def form_chessboard(positions):
    """
    Function forms chessboard from loaded positions.

    :param positions: figure positions for specific image
    """
    global chessboard
    for cl, x, y in positions:
        chessboard[y][x] = classes[cl]


def calculate_distance(x1, y1, x2, y2):
    """
    Function calculates distance between two points.

    :param x1:
    :param y1:
    :param x2:
    :param y2:
    :return: distance of two points
    """
    return math.sqrt(((x2 - x1) ** 2) + ((y2 - y1) ** 2))


def evaluate_chessboard(board_points, detected_points):
    """
    Function evaluates chessboard detection.

    :param board_points: board corners
    :param detected_points: detected corners
    :return: True if correct else False
    """
    global counter, correct_chessboard, incorrect_chessboard

    counter += 1

    if detected_points == -1:
        incorrect_chessboard += 1
        return False

    check = True

    for i in range(4):
        dist = calculate_distance(board_points[i][0], board_points[i][1],
                                  detected_points[i][0], detected_points[i][1])

        if dist > 40:
            check = False

    if check:
        correct_chessboard += 1
        return True
    else:
        incorrect_chessboard += 1
        return False


def calculate_correct_and_incorrect_positions(fen, detected_fen):
    """
    Function compares test FEN and detected FEN.

    :param fen: test FEN string
    :param detected_fen: detected FEN string
    """
    global detection_error, figure_correct, figure_incorrect

    # split string into rows
    rows = fen.split('/')
    rows_det = detected_fen.split('/')

    # for each row
    for i, row in enumerate(rows):
        row_det = rows_det[i]

        # same length
        if len(row) == len(row_det):

            # same rows
            if row == row_det:

                # every figure is placed in good place
                for char in row:
                    if char.isalpha():
                        figure_correct += 1

            # different rows
            else:
                for j, char in enumerate(row):

                    # char in row is figure
                    if char.isalpha():

                        # if figures are the same
                        if char == row_det[j]:
                            figure_correct += 1

                        # if figures arent the same
                        elif row_det[j].isalpha():
                            figure_incorrect += 1

                        # localization failed
                        else:
                            detection_error += 1

                    # char is number of spaces
                    else:

                        # its space too
                        if row_det[j].isalnum():
                            continue

                        # localization failed cuz it detected figure
                        else:
                            detection_error += 1

        # test FEN is longer
        elif len(row) > len(row_det):

            # going through detected row
            for j, char in enumerate(row_det):

                # figure detected
                if char.isalpha():

                    # correct figure
                    if char == row[j]:
                        figure_correct += 1

                    # wrong figure
                    elif row[j].isalpha():
                        figure_incorrect += 1

                    # wrong localization
                    else:
                        detection_error += 1

                # spaces
                else:

                    # space too
                    if row[j].isalnum():
                        continue

                    # localization failed
                    else:
                        detection_error += 1

            # difference in length
            num = len(row) - len(row_det)
            for j in range(len(row) - num, len(row)):

                # missing figure in detection
                detection_error += 1

        # detected FEN is longer
        else:

            # going through longer row
            for j, char in enumerate(row):

                # figure
                if char.isalpha():

                    # same figure
                    if char == row_det[j]:
                        figure_correct += 1

                    # different figure
                    elif row_det[j].isalpha():
                        figure_incorrect += 1

                    # wrong localization
                    else:
                        detection_error += 1

                # space
                else:

                    # space too
                    if row_det[j].isalnum():
                        continue

                    # wrong localization
                    else:
                        detection_error += 1

            # difference in length
            num = len(row_det) - len(row)

            for j in range(len(row_det) - num, len(row_det)):

                # wrong localization
                detection_error += 1


def evaluate_localization(fen, detected_fen):
    """
    Function evaluates test FEN and detected FEN by comparing.

    :param fen: test FEN string
    :param detected_fen: detected FEN string
    :return: True if same else False
    """
    global counter, correct_localization, incorrect_localization

    if fen == detected_fen:
        correct_localization += 1
        return True
    else:
        incorrect_localization += 1
        return False


def write_log(image, fen, detected_fen):
    """
    Function writes incorrect detections (even in one figure) to log file.

    :param image: image name
    :param fen: test FEN string
    :param detected_fen: detected FEN string
    """
    res = image + "\n" + fen + "\n" + detected_fen + "\n"

    with open(r"log.txt", "a") as f:
        f.write(res)
    f.close()


if __name__ == "__main__":
    directory = r''
    cfg = r''
    weights = r''

    if not directory:
        print("Enter directory first.")
        exit(1)

    # init figure detection
    fig_det = fD.FigureDetection() # net_cfg=cfg, net_weights=weights

    # init chessboard detection
    # chb_det = chDPC.ChessBoardDetection()
    chb_det = chDHl.ChessBoardDetection()

    images = load_images(directory)
    if not images:
        print('images load error')
        exit(1)

    timesBoard, timesFig = [], []
    figure_counter = 0
    complete_test_count = 0

    for image in images:
        chessboard = [
            ["_", "_", "_", "_", "_", "_", "_", "_"],
            ["_", "_", "_", "_", "_", "_", "_", "_"],
            ["_", "_", "_", "_", "_", "_", "_", "_"],
            ["_", "_", "_", "_", "_", "_", "_", "_"],
            ["_", "_", "_", "_", "_", "_", "_", "_"],
            ["_", "_", "_", "_", "_", "_", "_", "_"],
            ["_", "_", "_", "_", "_", "_", "_", "_"],
            ["_", "_", "_", "_", "_", "_", "_", "_"]
        ]

        pos = load_positions(image)
        if not pos:
            print("image doesnt have pos", image)
            break

        mid_point = load_middle_point(image)
        if not mid_point:
            print("image doesnt have middle point", image)
            break

        board = load_board(image)
        if not board:
            print("image doesnt have board corners", image)
            break

        # form chessboard from saved positions
        form_chessboard(pos)

        # create fen for check
        fen = fig_det.create_FEN(chessboard)

        # calculate figure count
        for char in fen:
            if char.isalpha():
                figure_counter += 1
                complete_test_count += 1

            if char.isalnum():
                complete_test_count += 1

        # chessboard detection
        chb_det.middle_point = mid_point
        chb_det.pressed = True
        try:
            startBoard = time.time()
            center_points, corners = chb_det.detect_chessboard(image, evaluation=True)
        except TypeError:
            counter += 1
            incorrect_chessboard += 1
            continue

        endBoard = time.time()

        if not evaluate_chessboard(board, corners):
            continue

        # figure detection
        startFig = time.time()
        detected_fen = fig_det.detect_figures(image, center_points)
        endFig = time.time()

        timesBoard.append((endBoard - startBoard))
        timesFig.append((endFig - startFig))

        # evaluate result
        if not evaluate_localization(fen, detected_fen):
            write_log(image, fen, detected_fen)
        calculate_correct_and_incorrect_positions(fen, detected_fen)

    print("counter: ", counter)
    print("avg time:", (sum(timesBoard) / len(timesBoard)) + (sum(timesFig) / len(timesFig)))
    print("avg board time:", sum(timesBoard) / len(timesBoard))
    print("avg fig time:", sum(timesFig) / len(timesFig))
    print("correct chessboard: ", correct_chessboard)
    print("incorrect chessboard: ", incorrect_chessboard)
    print("correct localization: ", correct_localization)
    print("incorrect localization: ", incorrect_localization)
    print("complete fen count:", complete_test_count)
    print("figure count:", figure_counter)
    print("correct figure detection: ", figure_correct)
    print("incorrect figure detection: ", figure_incorrect)
    print("error detection: ", detection_error)
