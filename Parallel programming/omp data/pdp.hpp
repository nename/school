#include <iostream>
#include <vector>
#include <algorithm>
#include <chrono>
#include <omp.h>
#include <string>
#include <fstream>
#include <sstream>

enum Color{
    Green,
    Red,
    Uncolored
};

typedef struct Edge{
    un_s first;
    un_s second;
    un_s value;

    Edge (un_s first, un_s second, un_s value){
        this->first = first;
        this->second = second;
        this->value = value;
    };
} Edge;

typedef struct State{
    vector<Color> vertices;
    vector<Edge> remaining;
    vector<Edge> current;
    un_s currVal;
    un_s remVal;

    // constructor
    State (vector<Color> vertices, vector<Edge> remaining, vector<Edge> current, un_s currVal, un_s remVal){
        this->vertices = vertices;
        this->remaining = remaining;
        this->current = current;
        this->currVal = currVal;
        this->remVal = remVal;
    };
} State;

typedef unsigned short int un_s;

unsigned int maxVal = 0;
vector<State> solutions;