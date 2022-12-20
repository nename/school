/**
 *  subject: IMS
 *  name of the procjet: Uhlikova stopa v doprave
 *  name of the file: ims.cpp
 *  description: main logic of project
 *  date: 8.12.2019
 * 
 *  authors: 
 *          xhampl10 (xhampl10@stud.fit.vutbr.cz)
 *          xsajdi01 (xsajdi01@stud.fit.vutbr.cz)
**/

#include "ims.hpp"

/**
 * function prints help
 **/
void printHelp(){
    cout << "===== HELP =====" << endl
            << "program run ./ims [-h -d {int} -t {int} -s {int} -v {float} -w {float}]" << endl
            << "ARGUMENTS" << endl
            << "-h : prints program help" << endl
            << "-d {int} : number of days that simulation should run" << endl
            << "-t {int} : number of trains" << endl
            << "-s {int} : number of seats in train" << endl
            << "-v {float} : maximal velocity of train in m/s" << endl
            << "-w {float} : weight of train" << endl
            << "----------------------------------" << endl
            << "You can run our experiments by calling \"make expI\", where I is <1,3>." << endl
            << "Experiment #1 shows the impact of velocity on energy and time of the travel." << endl
            << "Experiment #2 shows the difference of more seats versus more trains." << endl
            << "Experiment #3 shows same effect as #2 but in long term." << endl
            << "Additional information can be found in documentation." << endl;
    exit(0);
}

/**
 * function prints all results including setup arguments
 **/
void printOutput(){
    // setup data
    cout << "Num of days: " << days << endl;
    cout << "Num of trains: " << totalTrains << endl;
    cout << "Num of seats: " << totalSeats << endl;
    cout << "Train weight: " << trainWeight << " kg" << endl;
    cout << "Maximal velocity: " << velocity << " m/s" << endl << endl;

    // output
    cout << "Travel time: " << travelTime << " min" << endl;
    cout << "Total passengeds: " << passengers << endl;
    if (days < 100)
        cout << "Total energy needed: " << totalEnergy / 1000000 << " MJ" <<  endl;
    else
        cout << "Total energy needed: " << totalEnergy / 1000000000 << " GJ" <<  endl;
    cout << "Total CO2 emition: " << CO2 / 1000 << " kg" <<endl;
    cout << "Diesel train CO2 emition: " << dieselTrainCO2 << " kg" << endl;
}

/**
 * function calculates CO2 rate from energy
 **/
void calculateCO2(){
    double wattHours = totalEnergy / 3600; // Wh = J / 3600
    CO2 = ((wattHours / 1000) * 200 ); // 1 kWh = 200 g CO2
}

/**
 * function parses arguments and checks validity 
 * 
 * @param int argc number of arguments
 * @param char** argv arguments
 **/
void parseArguments(int argc, char** argv){
    int opt;
    regex i ("^[0-9]+$");
    regex d ("^[0-9]+(.{0,1}[0-9])*$");
    
    while ((opt = getopt(argc, argv, "hd:t:s:v:w:")) != EOF){
        switch (opt){
            case 'h' :
                printHelp();
                break;

            case 'd' :
                if (regex_match(optarg, i)){
                    days = atoi(optarg);
                    if (days == 0){
                        days = DEFAULT_NUM_DAYS;
                        cerr << "Number of days can not be 0." << endl;
                    }
                } else {
                    cerr << "Number of days has to be integer." << endl;
                }
                break;

            case 't' :
                if (regex_match(optarg, i)){
                    totalTrains = atoi(optarg);
                    if (totalTrains == 0){
                        totalTrains = DEFAULT_NUM_TRAINS;
                        cerr << "Number of trains can not be 0." << endl;
                    }
                } else {
                    cerr << "Number of trains has to be integer." << endl;
                }
                break;            

            case 's' :
                if (regex_match(optarg, i)){
                    totalSeats = atoi(optarg);
                    if (totalSeats == 0){
                        totalSeats = DEFAULT_NUM_SEATS;
                        cerr << "Number of seats can not be 0." << endl;
                    }
                } else {
                    cerr << "Number of seats has to be integer." << endl;
                }
                break;

            case 'v' :
                if (regex_match(optarg, d)){
                    velocity = atof(optarg);
                    if (velocity == 0){
                        velocity = DEFAULT_VELOCITY;
                        cerr << "Velocity can not be 0." << endl;
                    }
                } else {
                    cerr << "Velocity has to be float number." << endl;
                }
                break;

            case 'w' :
                if (regex_match(optarg, d)){
                    trainWeight = atof(optarg);
                    if (trainWeight == 0){
                        trainWeight = DEFAULT_TRAIN_WEIGHT;
                        cerr << "Train weight can not be 0." << endl;
                    }
                } else {
                    cerr << "Train weight has to be flaot nubmer." << endl;
                }
                break;

            case '?' : 
                cerr << "Wrong input argument. Call argument \"-h\" for help." << endl;
                break;

            default : break;
        }
    }
}

/**
 * public process that represents actions of train
 **/
class Train : public Process {
public:
    unsigned int seats = 0;
    double energy = 0.0;
    double weight = trainWeight;
    double accelDist = (velocity / DEFAULT_VELOCITY) * DEFAULT_ACCEL_DISTANCE;
    double accelTime = accelDist / velocity;
    double acceleration = velocity / accelTime;
    double brakeDist = pow(velocity, 2) / (2 * STEEL_FRICTION * GRAVITY_ACCEL);
    double wheelFriction = Uniform(0.0002, 0.001);

    /**
     * function calculates forces that needs to be overcome
     * 
     * @param double dist distance of traveling
     **/
    void calculateEnergy(double dist){ 
        double dragForce = 0.5 * RESISTANCE_COEFFICIENT * AIR_DENSITY * pow(velocity, 2)  * TRAIN_FRONTAL_AREA;
        double accelForce = weight * acceleration;
        double accelEnergy = (accelForce + accelForce * wheelFriction) * accelDist ;
        double travelEnergy = (dragForce + dragForce * wheelFriction) * (dist - accelDist - brakeDist);
        energy += accelEnergy + travelEnergy;
    }

    /**
     * function represents behavior of the train
     **/
    void Behavior(){
        unsigned int leave, board;
        leave = board = 0;
        
        for (unsigned int i = 0; i < dist.size(); i++){
            // train entering a stop
            Wait(Uniform(1,3) * MINUTE);

            // leaving
            if (seats != 0){
                if (i < 14)
                    leave = Uniform(0.0, (ppl[i] * 0.025 / seats));
                else
                    leave = seats;
                
                if (leave > seats)
                    leave = seats;
            }

            weight -= leave * AVG_HUMAN_WEIGHT;
            seats -= leave;

            // boarding
            if (i < 14)
                board = Uniform(0.0 , (ppl[i] * 0.025) / ((double)totalTrains));
            else
                board = 0;

            if (board + seats > totalSeats)
                board = totalSeats - seats;

            weight += board * AVG_HUMAN_WEIGHT;
            seats += board;

            passengers += board;

            // wait time of traveling
            Wait((((dist[i] - accelDist) / velocity) + accelTime) / MINUTE);

            travelTime += ((dist[i] - accelDist) / velocity + accelTime) / MINUTE;

            // how much CO2 does diesel train emit (21g per person on 1 km) in kg
            dieselTrainCO2 += (seats * (dist[i]/1000) * 21) / 1000;

            calculateEnergy(dist[i]);
        }
         
        totalEnergy += energy;
        Terminate();
    }
};

/**
 * generator for trains
 **/
class Generator : public Event {
    void Behavior(){
        for (unsigned int i = 0; i < totalTrains; i++){
            (new Train)->Activate(Time + ((DEFAULT_NUM_TRAINS / totalTrains) * DEFAULT_TIME_BETWEEN_TRAINS));
        }
        Activate(Time + 24 * HOUR);
    }
};

int main (int argc, char ** argv){
    parseArguments(argc, argv);

    Init(0, days * 24 * HOUR);

    RandomSeed(time(NULL));

    (new Generator)->Activate();
    Run();

    calculateCO2();
    printOutput();
    return 0;
}