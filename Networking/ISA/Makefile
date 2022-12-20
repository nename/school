CC = g++
FLAGS = -std=gnu++14

all:
	$(CC) $(FLAGS) api.cpp -o api.o -c
	$(CC) $(FLAGS) isaserver.cpp -o isaserver api.o
	$(CC) $(FLAGS) isaclient.cpp -o isaclient
rm:
	rm api.o
	rm isaserver
	rm isaclient