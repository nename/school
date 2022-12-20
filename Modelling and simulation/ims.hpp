/**
 *  subject: IMS
 *  name of the procjet: Uhlikova stopa v doprave
 *  name of the file: ims.hpp
 *  description: library for ims.cpp
 *  date: 8.12.2019
 * 
 *  authors: 
 *          xhampl10 (xhampl10@stud.fit.vutbr.cz)
 *          xsajdi01 (xsajdi01@stud.fit.vutbr.cz)
**/
#ifndef IMS_HPP
#define IMS_HPP

#include <iostream>
#include <string>
#include "simlib.h"
#include <ctype.h>
#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <vector>
#include <cmath>
#include <regex>
#include <numeric>

// default values
#define DEFAULT_NUM_DAYS 1
#define DEFAULT_NUM_SEATS 160
#define DEFAULT_TRAIN_WEIGHT 65000 // kg
#define DEFAULT_NUM_TRAINS 6
#define DEFAULT_VELOCITY 22.2 // m/s^2
#define DEFAULT_TIME_BETWEEN_TRAINS (HOUR * 2)
#define DEFAULT_ACCELERATION 0.4
#define DEFAULT_ACCEL_DISTANCE 1200

// other constants
#define MINUTE 60
#define HOUR (MINUTE * 60)
#define AVG_HUMAN_WEIGHT 70 // kg
#define STEEL_FRICTION 0.6 // steel on steel friction
#define GRAVITY_ACCEL 9.81 
#define RESISTANCE_COEFFICIENT 0.6 // resistance coefficient of train
#define AIR_DENSITY 1.2041 // air density at 20 degrees
#define TRAIN_FRONTAL_AREA 15 // m^2

using namespace std;

// globas
unsigned int days = DEFAULT_NUM_DAYS;
unsigned int totalTrains = DEFAULT_NUM_TRAINS;
unsigned int totalSeats = DEFAULT_NUM_SEATS;
double velocity = DEFAULT_VELOCITY;
double trainWeight = DEFAULT_TRAIN_WEIGHT;
double totalEnergy = 0.0;
double CO2 = 0.0;
double passengers = 0.0;
double travelTime = 0.0;
double dieselTrainCO2 = 0.0;

// vector of distances between stops, track Rumburk - Kolin
vector <double> dist = {6000, 5000, 9000, 8000, 5000, 4000, 6000, 2000, 15000, 20000, 10000, 9000, 30000, 7000, 16000}; // Rumburk - Kolín

// vector of population of each stop
vector <double> ppl = {11000, 3490, 662, 992, 628, 11679, 18733, 18734, 5191, 4890, 4978, 44327, 15054, 14192};

// classes
class Generator;
class Train;

// functions
void printHelp();
void printOutput();
void calculateCO2();
void parseArguments(int argc, char** argv);
#endif