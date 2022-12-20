#!/bin/usr/env python
"""
Helper module for annotation check.

Author: Tomáš Hampl xhampl10 (xhampl10@stud.fit.vutbr.cz)
"""

import os
import cv2 as cv

path = r'PATH'

images = []

def getFigureName(fig):
    return {
        0: "wp",
        1: "wb",
        2: "wkn",
        3: "wr",
        4: "wq",
        5: "wk",
        6: "bp",
        7: "bb",
        8: "bkn",
        9: "br",
        10: "bq",
        11: "bk",
    }[fig]

# get all images
for file in os.listdir(path):
    if file.endswith('.jpeg') or file.endswith('.JPEG') or file.endswith('.png') or file.endswith('.jpg'):
        images.append(os.path.join(path, file))

# for each image check if annotations are correct
for image in images:
    txt = image.split('.')[0] + '.txt'

    if not os.path.isfile(txt):
        print("image doesnt have txt file: ", image)

    img = cv.imread(image)
    img = cv.resize(img, (600, 600))
    font = cv.FONT_HERSHEY_SIMPLEX

    print(image)

    with open(txt, "r") as f:
        for line in f.readlines():
            line = line.rstrip()
            fig, x, y, w, h = list(map(float, line.split(' ')))

            if int(fig) in [1, 3, 4, 9]:

                figure = getFigureName(int(fig))

                l = int((x - w / 2) * 600)
                r = int((x + w / 2) * 600)
                t = int((y - h / 2) * 600)
                b = int((y + h / 2) * 600)

                x = int(float(x) * 600)
                y = int(float(y) * 600)

                if l < 0:
                    l = 0
                if r > 600 - 1:
                    r = 600 - 1
                if t < 0:
                    t = 0
                if b > 600 - 1:
                    b = 600 - 1

                img = cv.rectangle(img, (l, t), (r, b), (0, 255, 0), 1)

                cv.putText(img, figure, (x - 10, y), font, 1, (0, 0, 255), 2, cv.LINE_AA)

    cv.imshow("Text", img)
    cv.waitKey(0)

print("done")
cv.destroyAllWindows()
