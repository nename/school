/*
 * IZP projekt #2 - iterační výpočty
 * Tomáš Hampl - xhampl10
 */
 
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <math.h>
#define PI 3.14159265359
#define iterace 10

/*
 * function: taylor_tan
 * --------------------
 * INPUT: double x, unsigned int n
 * RETURN: double result
 * REMARKS: using the Taylor polynomial calculates tangent of x within n iterations
 */
double taylor_tan (double x, unsigned int n) {
    double numerator[] = {1, 1, 2, 17, 62, 1382, 21844, 929569, 6404582, 443861162, 18888466084, 113927491862, 58870668456604};
    double denominator[] = {1, 3, 15, 315, 2835, 155925, 6081075, 638512875, 10854718875, 1856156927625, 194896477400625, 49308808782358125, 3698160658676859375};
    double result = 0.;
    double Presult;
    double mocX = x * x;
    for (unsigned int i = 0; i<n; i++){
        Presult = (numerator[i] * x / denominator[i]);
        result = Presult + result;
        x = mocX * x;
    }
    return result;
}
/*
 * function: taylor_tan
 * --------------------
 * INPUT: double x, unsigned int n
 * RETURN: double result
 * REMARKS: using continued fraction calculates tangent of x within n iterations
 */
double cfrac_tan(double x, unsigned int n) {
    double up = 1.0;
    double down;
    double result = 0.;
    for (; n>= 1; n--){
        down = (2*n-1)/x;
        result = up/(down - result);
    }
    return result;
}
/*
 * function: taylor_tan_difference
 * -------------------------------
 * INPUT: double x, unsigned int n
 * RETURN: double
 * REMARKS: calculates difference between tan from math.h and function taylor_tan
 */
double taylor_tan_difference (double x, unsigned int n){
    return tan(x) - taylor_tan(x, n);
}
/*
 * function: cfrac_tan_difference
 * ------------------------------
 * INPUT: double x, unsigned int n
 * RETURN: double
 * REMARKS: calculates difference between tan from math.h and function cfrac_tan
 */
double cfrac_tan_difference (double x, unsigned int n){
    return tan(x) - cfrac_tan(x, n);
}
/*
 * function: printf_tan
 * --------------------
 * INPUT: double alfa, int n, int m
 * RETURN: void (just prints)
 * REMARKS: by using simple cycle prints --tan
 */
void printf_tan (double alfa, unsigned int n,unsigned int m){
    for (; n <= m; n++) {
        printf("%d %e %e %e %e %e\n", n, tan(alfa), fabs(taylor_tan(alfa, n)), fabs(taylor_tan_difference(alfa, n)),
               fabs(cfrac_tan(alfa, n)), fabs(cfrac_tan_difference(alfa, n)));
    }
}
/*
 * function: calc_distance
 * ----------------------
 * INPUT: double alfa, double device_height
 * RETURN: distance
 * REMARKS: calculates distance
 */
double calc_distance (double alfa, double device_height){
	double distance = device_height / cfrac_tan(alfa, iterace);
	printf("%.10e\n",distance);
	return distance;
}
/*
 * function: calc_height
 * ---------------------
 * INPUT: double beta, double distance, double device_height
 * RETURN: double height
 * REMARK: calculates height
 */
double calc_height (double beta, double distance, double device_height){
	double height = distance * cfrac_tan(beta, iterace);
	printf("%.10e\n",height + device_height);
	return height;
}

// prints error message
void angle_error (void){ 														
	fprintf(stderr, "Angle has to be within (0,PI/2).\n");
} 

// prints error message
void wrong_input (void){ 														
	fprintf(stderr, "Wrong input. Please put '--help' as argument to get instructions.\n");
}


int main(int argc, char *argv[]){
    // check if there is input, if not prints error message
    if (argc == 1) { 															 
        wrong_input(); 
        return 1; 
    // --help, prints instructions
    } else if (strcmp(argv[1], "--help") == 0){ 											
        printf("Usage:\n./proj2 --help\n./proj2 --tan A N M\n./proj2 [-c X] -m A [B]\n");
    
    // argument --tan
    } else if (strcmp(argv[1], "--tan") == 0){	 											
        // checks if there is enough arguments to continue
        if (argc != 5){ 														
            wrong_input(); return 1;
        }
        // checks if alfa is within limitation
        double alfa = atof(argv[2]); 													
        if (alfa > 0 && alfa < (PI/2)){
            int n = atoi(argv[3]);
            int m = atoi(argv[4]);

            // checks if arguments n, m (number of iterations) are correct
            if (n > 0 && n < 14 && m > 0 && m < 14 && n <= m){										
		        printf_tan(alfa, n, m);
	        } else { 
                fprintf(stderr, "Parametres n and m has to be within (0,14) and n <= m.\n"); 
                return 1;
            }
        } else { 
            angle_error(); 
            return 1;
        }
    // arguments -c and -m : converts arguments, computes with them and prints results
    } else if (strcmp(argv[1], "-c") == 0 && strcmp(argv[3], "-m") == 0){ 								
        // checks number of arguments
        if (argc < 5 || argc > 6){													
            wrong_input(); 
            return 1;
        }

        double device_height = atof(argv[2]);
        double alfa = atof(argv[4]);
        // checks values of arguments
        if (alfa > 0 && alfa < (PI/2) && device_height > 0 && device_height <= 100){							
            double distance = calc_distance(alfa, device_height);
            
            if (argc == 6){
		        double beta = atof(argv[5]);
                if (beta > 0 && beta < (PI / 2)){
                    calc_height(beta, distance, device_height);
                } else { 
                    angle_error(); 
                    return 1; 
                }
            }
        } else { 
            fprintf(stderr, "Alfa has to be within (0,PI/2) and height of the device has to be within (0,100>.\n"); 
            return 1;
        }
    // argument -m
    } else if (strcmp(argv[1], "-m") == 0){ 		
        // checks number of arguments										
        if (argc < 3 || argc > 4){ 													
            wrong_input(); 
            return 1;
        }

        double device_height = 1.5;
        double alfa = atof(argv[2]);

        // checks if alfa is within limitation
        if (alfa > 0 && alfa < (PI/2)){ 												
            double distance = calc_distance(alfa, device_height);

            // if there are 4 arguments, calculates height
            if (argc == 4){ 														
                double beta = atof(argv[3]);
                if (beta > 0 && beta < (PI / 2)){
                    calc_height(beta, distance, device_height);
                } else { 
                    angle_error(); 
                    return 1;
                }
            }
        } else { 
            angle_error(); 
            return 1;
        }
    } else { 
        wrong_input(); 
        return 1;
    }
    return 0;
} 
