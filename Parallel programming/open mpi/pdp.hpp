#include <iostream>
#include <vector>
#include <algorithm>
#include <chrono>
#include <omp.h>
#include <boost/archive/text_oarchive.hpp>
#include <boost/archive/text_iarchive.hpp>
#include <boost/serialization/vector.hpp>
#include <iomanip>
#include <sstream>
#include <mpi.h>
#include <string>
#include <fstream>

#define TAG_JOB 1
#define TAG_RES 2
#define TAG_END 3

typedef unsigned short int un_s;

enum Color{
    Green,
    Red,
    Uncolored
};

class Edge{
    friend class boost::serialization::access;
    template <class Archive> void serialize(Archive& ar, unsigned){
        ar &first;
        ar &second;
        ar &value;
    }

    public:
    un_s first;
    un_s second;
    un_s value;

    Edge () {};
    Edge (un_s first, un_s second, un_s value){
        this->first = first;
        this->second = second;
        this->value = value;
    };
};

class State{
    friend class boost::serialization::access;
    template <class Archive> void serialize(Archive& ar, unsigned){
        ar &vertices;
        ar &remaining;
        ar &current;
        ar &currVal;
        ar &remVal;
		ar &maxVal;
    }

    public:
    vector<Color> vertices;
    vector<Edge> remaining;
    vector<Edge> current;
    un_s currVal;
    un_s remVal;
	unsigned int maxVal;

    State () {};
    State (vector<Color> vertices, vector<Edge> remaining, vector<Edge> current, un_s currVal, un_s remVal){
        this->vertices = vertices;
        this->remaining = remaining;
        this->current = current;
        this->currVal = currVal;
        this->remVal = remVal;
		this->maxVal = 0;
    };
};