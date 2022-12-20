#!/usr/bin/env python
"""
Module that implements simple pyqt 5 app for demonstration.

Author: Tomáš Hampl xhampl10@stud.fit.vutbr.cz
"""

import sys
import os

import chess
import chess.svg
from PyQt5.QtSvg import QSvgWidget
from PyQt5.QtWidgets import QApplication, QWidget, QLabel, QLineEdit, QPushButton, QMainWindow
from PyQt5.QtGui import QPixmap

from figureDetection import FigureDetection
from chessboardDetectionPClines import ChessBoardDetection as PC
from chessboardDetectionHoughlines import ChessBoardDetection as HT


def check_file(path):
    """
    Helper function if file exists.

    :param path: image path
    :return: True or False based on file existing
    """
    if os.path.isfile(path):
        return True
    return False


class MainWindow(QMainWindow):
    """
    Main window module.
    """
    def __init__(self, net_cfg=r'./yolov4.cfg', net_weights=r'./yolov4.weights'):
        """
        Innit function.

        :param net_cfg: configuration of neural network
        :param net_weights: configuration of weights
        """
        super().__init__()

        # pointers to instances
        self.image_window = None
        self.chessboard_window = None
        if check_file(net_cfg):
            self.net_cfg = net_cfg
        else:
            print("missing CNN cfg")

        if check_file(net_weights):
            self.net_weights = net_weights
        else:
            print("missing CNN weights")

        # window setup
        self.setGeometry(100, 100, 590, 100)
        self.setWindowTitle("Lokalizace šachovnice")

        # name label
        self.name_label = QLabel(self)
        self.name_label.setText('Adresa obrázku:')
        self.name_label.move(20, 15)

        # line label
        self.line = QLineEdit(self)
        self.line.move(105, 20)
        self.line.resize(450, 20)

        # image button
        image_button = QPushButton('Načíst obrázek', self)
        image_button.clicked.connect(self.create_image_window)
        image_button.resize(120, 30)
        image_button.move(20, 60)

        # chessboard button - detection PClines
        chessboard_button_PC = QPushButton('Lokalizovat šachovnici - PClines', self)
        chessboard_button_PC.clicked.connect(self.create_chessboard_window_PC)
        chessboard_button_PC.resize(200, 30)
        chessboard_button_PC.move(150, 60)

        # chessboard button - detection Houghlinesp
        chessboard_button_HT = QPushButton('Lokalizovat šachovnici - Houghlines', self)
        chessboard_button_HT.clicked.connect(self.create_chessboard_window_HT)
        chessboard_button_HT.resize(200, 30)
        chessboard_button_HT.move(360, 60)

    def create_image_window(self):
        """
        Function creates image window that displays image.
        """
        if self.image_window is None:
            self.image_window = ImageWindow()
        if check_file(self.line.text()):
            self.image_window.change_image(self.line.text())
            self.image_window.show()

    def create_chessboard_window_PC(self):
        """
        Function creates chessboard window that displays detected chessboard.
        """
        if self.chessboard_window is None:
            self.chessboard_window = ChessboardWindow()

        if check_file(self.line.text()):
            print("chessboard detection PC")
            bd = PC()
            center_points = bd.detect_chessboard(self.line.text())
            print("chessboard detection done")

            if center_points == -1:
                print("chessboard detection error")
                return

            print("figure detection")
            fd = FigureDetection(net_cfg=self.net_cfg, net_weights=self.net_weights)
            fen = fd.detect_figures(self.line.text(), center_points)
            print("figure detection done")

            self.chessboard_window.draw_board(fen)
            self.chessboard_window.show()

    def create_chessboard_window_HT(self):
        """
        Function creates chessboard window that displays detected chessboard.
        """
        if self.chessboard_window is None:
            self.chessboard_window = ChessboardWindow()

        if check_file(self.line.text()):
            print("chessboard detection HT")
            bd = HT()
            center_points = bd.detect_chessboard(self.line.text())
            print("chessboard detection done")

            if center_points == -1:
                print("chessboard detection error")
                return

            print("figure detection")
            fd = FigureDetection(net_cfg=self.net_cfg, net_weights=self.net_weights)
            fen = fd.detect_figures(self.line.text(), center_points)
            print("figure detection done")

            self.chessboard_window.draw_board(fen)
            self.chessboard_window.show()


class ImageWindow(QWidget):
    """
    Widget for image display.
    """
    def __init__(self):
        super().__init__()
        self.setGeometry(100, 400, 600, 600)
        self.setWindowTitle("Image view")
        self.label = QLabel(self)

    def change_image(self, path):
        """
        Function that actualizes image.

        :param path: image path
        """
        pixmap = QPixmap(path)
        pixmap2 = pixmap.scaled(600, 600)
        self.label.setPixmap(pixmap2)


class ChessboardWindow(QWidget):
    """
    Widget for chessboard svg display.
    """
    def __init__(self):
        super().__init__()
        self.setGeometry(800, 400, 600, 600)
        self.setWindowTitle("Result")

        self.widgetSvg = QSvgWidget(parent=self)
        self.widgetSvg.setGeometry(10, 10, 580, 580)

    def draw_board(self, fen):
        """
        Function that actualizes chessboard.

        :param fen: Forsyth-Edwards Notation
        """
        chessboard = chess.Board(fen)
        chessboard_svg = chess.svg.board(chessboard).encode("UTF-8")
        self.widgetSvg.load(chessboard_svg)


if __name__ == "__main__":
    #netCfg = r""
    #netWeights = r""

    app = QApplication(sys.argv)
    w = MainWindow() # netCfg, netWeights
    w.show()
    app.exec_()
