CC = g++
FLAGS = -Wall -Wextra -pedantic -std=gnu++14
SIMLIB = -lsimlib -lm

all:
	$(CC) $(FLAGS) ims.cpp -o ims $(SIMLIB)

rm:
	rm -f ims

run:
	./ims

exp1:
	./ims -t 1 -d 10
	./ims -t 1 -d 10 -v 15

exp2:
	./ims -t 1 -s 250
	./ims -t 2 -s 125

exp3:
	./ims -t 1 -s 250 -d 30
	./ims -t 2 -s 125 -d 30

