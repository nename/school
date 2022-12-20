#include <iostream>
#include <vector>
#include <algorithm>
#include <omp.h>
#include <chrono>
#include <string>
#include <fstream>
#include <sstream>

typedef unsigned short int un_s;

enum Colour{
    Green,
    Red,
    Uncolored
};

typedef struct{
    un_s first;
    un_s second;
    un_s value;
} Edge;

unsigned int maxVal = 0;
vector<pair<vector<Colour>, vector<Edge>>> solutions;
