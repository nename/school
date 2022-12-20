"""
Program plots avg loss and avg precision from log file.

author : Tomáš Hampl (xhampl10@stud.fit.vutbr.cz)
"""

import matplotlib.pyplot as plt
import numpy as np
import sys


iterations = []
map_iterations = []
losses = []
mean_avg_precision = []

step = 100

# log path
file = r''

if len(sys.argv) > 1:
    file = sys.argv[1]
    if len(sys.argv) > 2:
        step = sys.argv[2]

f = open(file, 'r')

for line in f.readlines():
    # loss + iterations
    if line.strip().endswith('left'):

        # split line to get iteration
        iteration = line.split(':')[0]

        # filter out avg loss and append
        if int(iteration) == 1:
            iterations.append(int(iteration))

            # append loss
            loss = line.split(',')[1].strip()

            loss = loss.split(' ')[0]

            losses.append(float(loss))

        # check if its correct iteration
        if int(iteration) % step == 0:
            if int(iteration) in iterations:
                continue

            iterations.append(int(iteration))

            # filter out avg loss and append
            loss = line.split(',')[1].strip()

            loss = loss.split(' ')[0]

            losses.append(float(loss))

    # map
    if line.strip().startswith('mean_average_precision'):
        map = line.strip().split(' ')[-1]

        if iterations[-1] not in map_iterations:
            map_iterations.append(iterations[-1])
            mean_avg_precision.append(float(map) * 100)

f.close()

# plot avg loss
fig, ax = plt.subplots()
ax.plot(iterations, losses)
plt.title("YOLOv4")
plt.xlabel('iterace')
plt.ylabel('průměrná ztráta')
ticks = np.arange(0.0, 20.0, 2.0)
ax.set_yticks(ticks)
plt.ylim(0.0, 20.0)
plt.grid()
plt.show()

# plot avg precision
fig2, ax2 = plt.subplots()
ax2.plot(map_iterations, mean_avg_precision)
plt.title("YOLOv4")
plt.xlabel('iterace')
plt.ylabel('průměrná přesnost')
ticks = np.arange(0.0, 110.0, 10.0)
ax2.set_yticks(ticks)
plt.grid()
plt.show()
