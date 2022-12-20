/*
 * IZP projekt #1 - práce s textem
 * Tomáš Hampl - xhampl10
 */

#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <ctype.h>

#define maxchars 101 															// sets the size of arrays
#define maxlines 42 															// sets how many cities/lines will the program use from adresy.txt

/*
 * function: UpperCase
 * --------------------
 * INPUT: string 
 * RETURN: void
 * REMARKS: takes string and makes uppercase
 */

void UpperCase (char *str){
	while(*str != '\0'){
		*str = toupper(*str);
		str++;
	}
}

/*
 *function: SortAlphabet
 *-------------------------
 * INPUT: string
 * RETURN: void
 * REMARKS: compares character from alphabet with string and prints it; doesnt allow use of other characters than letters
 */

void SortAlphabet (char *str){
	for (char k = 'A'; k <= 'Z'; k++){
		if (strchr(str,k) != NULL){
			printf("%c", k);
		}
	}

}

int main(int argc,char *argv[]){
	int j = 0;
	int k = 0;
	int idx = 0;
	char line[maxchars];
	char letters[27];														// 26 letters in alphabet
	char city[maxchars];
	
	letters[0] = '\0';	
	
	const char *vstup = argc == 1 ? "" : argv[1]; 											// if there is no argument, sets empty string
	
	int i = strlen(vstup);
	
	if (argc == 2){
		UpperCase(argv[1]);
	} 
	
	if (i >= maxchars){
		fprintf(stderr, "Lenght of argument is limited by: %d characters. Program will work only with them./n", maxchars);
		i = maxchars;
	}
	
	if (argc > 2){
		fprintf(stderr, "You can use only one argument.\n");									// if there is more arguments than one
	} else {
		while (fgets(line, maxchars, stdin) != NULL){ 										// goes throught stdin (file) and takes whole lines and sets it into string "line"
			UpperCase(line);
			if ((int) strlen(line) <= i || strncmp(vstup, line, i) != 0){
				continue;
			} 
			char c = line[i];
			if(strchr(letters, c) == NULL){ 										// compares empty array with c (letter from line on position i) > always will be NULL
				letters[idx++] = c; 											// saves next letter in array line
				letters[idx] = '\0';											// reset
			}
			k++;														// simple calculators
			j++;		 
			strcpy(city, line); 												// saves string line into string city
			if (k == maxlines) {
				fprintf(stderr, "Too many lines in stdin (file). Program will use only %d.\n", maxlines);	        // limit how many lines will program read from file, breaks if there is more lines
				break;
			}	
		}
		if (idx == '\0'){ 
			printf("Not found\n");
		} else if (j == 1){													// if the cycle goes through only once, there is a match, other options dont exist
			printf("Found: %s",city);
		} else {
			printf("Enable: ");	
			SortAlphabet(letters);
			printf("\n");		
		}	
	}
	return 0;
}
