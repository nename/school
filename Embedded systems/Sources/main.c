/*
 * Subject: IMP (Microprocessors and Embedded Systems)
 * Project: B - ARM-FITkit3: Dekodér Morseovy abecedy
 * Author: xhampl10 (Hampl Tomáš, xhampl10@stud.fit.vutbr.cz)
 * Date: 20.12.2019
 * Authorship: I took inspiration from our exercises in terms of initialization functions, UART communication and speaker function and from FITkit3-demo in terms of pin defines.
 * 			   Overall authorship percentage is ~77%. (Completely excluding initialization functions that are modified, speaker function, UART functions and defines.)
 */

#include "MK60D10.h"
#include <string.h>

// defined values
#define MAX_LENGTH 6 // max length of input arr
#define END_INPUT_DELAY 0x800
#define NUM_OF_LT 26 // number of letters
#define NUM_OF_CODES 36 // number of codes

// defined pins
#define BTN 0x4000000
#define SPK 0x10

// globals
const char morseCodes[NUM_OF_CODES][MAX_LENGTH] = {
		".-", "-...", "-.-.", "-..", ".", "..-.", // letters [26]
		"--.", "....", "..", ".---", "-.-",".-..",
		"--", "-.", "---", ".--.", "--.-", ".-.",
		"...", "-", "..-", "...-", ".--", "-..-",
		"-.--", "--..",

		"-----", ".----", "..---", "...--", "....-", // numbers [10]
		".....", "-....", "--...", "---..", "----."
};

enum States { // states for interrupt decisions
	PRESSED,
	UNPRESSED,
	NONE
};

int state = NONE; // current state
char input[MAX_LENGTH] = ""; // input array
int inputPosition = 0; // starting position

////////////////////////////////	INIT	////////////////////////////////

void MCUInit(void){
    MCG_C4 |= (MCG_C4_DMX32_MASK | MCG_C4_DRST_DRS(0x01));
    SIM_CLKDIV1 |= SIM_CLKDIV1_OUTDIV1(0x00); // clock
    WDOG_STCTRLH &= ~WDOG_STCTRLH_WDOGEN_MASK; // watchdog
}

void portInit(void){
	// CLOCK ENABLE
	SIM->SCGC1 |= SIM_SCGC1_UART5_MASK;
	SIM->SCGC5 |= SIM_SCGC5_PORTA_MASK | SIM_SCGC5_PORTE_MASK;

	// PIN SETUP
	PORTA->PCR[4] = PORT_PCR_MUX(0x01); // speaker

	PORTE->PCR[8]  = PORT_PCR_MUX(0x03); // UART0_TX
	PORTE->PCR[9]  = PORT_PCR_MUX(0x03); // UART0_RX
	PORTE->PCR[26] = PORT_PCR_MUX(0x01); // button SW5

	// speaker as output
	PTA->PDDR = GPIO_PDDR_PDD(0x10);
	PTA->PDOR &= GPIO_PDOR_PDO(~0x10);   // Speaker off
}

void UART0Init(void){
    UART5->C2 &= ~(UART_C2_TE_MASK | UART_C2_RE_MASK); // turn off
    UART5->BDH = 0x00; // setup
    UART5->BDL = 0x1A;
    UART5->C4 = 0x0F;
    UART5->C1 = 0x00;
    UART5->C3 = 0x00;
    UART5->MA1 = 0x00;
    UART5->MA2 = 0x00;
    UART5->S2 |= 0xC0;
    UART5->C2 |= (UART_C2_TE_MASK | UART_C2_RE_MASK); // turn on
}

void LPTMR0Init(void)
{
    SIM_SCGC5 |= SIM_SCGC5_LPTIMER_MASK; // enable clock
    LPTMR0_CSR &= ~LPTMR_CSR_TEN_MASK;   // Turn OFF
    LPTMR0_PSR = (LPTMR_PSR_PRESCALE(0) // setup
                 | LPTMR_PSR_PBYP_MASK
                 | LPTMR_PSR_PCS(1));
    LPTMR0_CSR =(LPTMR_CSR_TCF_MASK | LPTMR_CSR_TIE_MASK);    // Clear any pending interrupt and enable
    NVIC_EnableIRQ(LPTMR0_IRQn);         // enable interrupts
}

////////////////////////////////	LOGIC	////////////////////////////////

void startTimer(unsigned int cmr){
	LPTMR0_CSR &= ~LPTMR_CSR_TEN_MASK; // turn off
	LPTMR0_CMR = cmr; // set value for timer
	LPTMR0_CSR |= LPTMR_CSR_TEN_MASK; // turn on
}

void stopTimer(void){
	LPTMR0_CSR &= ~LPTMR_CSR_TEN_MASK; // turn off
}

void delay(unsigned int count){ // delay with no action
	for(unsigned long int i = 0; i < count; i++) __NOP();
}

void beep(unsigned int count) { // speaker
    for (unsigned int i = 0; i < count; i++) {
        PTA->PDOR |= GPIO_PDOR_PDO(SPK); // turn on
        delay(count);
        PTA->PDOR &= ~GPIO_PDOR_PDO(SPK); // turn off
        delay(count);
    }
}

void sendChar(char c) { // send char through UART
    while(!(UART5->S1 & UART_S1_TDRE_MASK) && !(UART5->S1 & UART_S1_TC_MASK));
    UART5->D = c;
}

void sendStr(char* s){ // send array of letters
	unsigned int i = 0;
	while (s[i] != '\0') sendChar(s[i++]);
}

int decodeMorse(void){
	char offset = 48; // if input position is less than 5 : letter else number
	if (inputPosition < 5) offset = 65;

	for (unsigned char i = 0; i < NUM_OF_CODES; i++){
		if (strcmp(input, morseCodes[i]) == 0){ // cmp
			if (i >= 26)
				sendChar(i - NUM_OF_LT + offset); // we have to drawback number of letters because of ascii
			else
				sendChar(i + offset);
			return 0;
		}
	}
	return 1;
}

void inputReset(void){ // clear input array
	inputPosition = 0;
	for (int i = 0; i < MAX_LENGTH; i++) input[i] = '\0';
}

int shortPress(void){
	if (inputPosition >= MAX_LENGTH){ // check input position
		sendStr("\nwrong input\n");
		inputReset();
		return 1;
	} else { // add .
		beep(300);
		input[inputPosition++] = '.';
	}
	return 0;
}

int longPress(void){
	if (inputPosition >= MAX_LENGTH){ // check input position
		sendStr("\nwrong input\n");
		inputReset();
		return 1;
	} else { // add -
		beep(700);
		input[inputPosition++] = '-';
	}
	return 0;
}

void LPTMR0_IRQHandler(void){ // handles time interrupt
	stopTimer();

	switch (state){
		case PRESSED : // button is held
			if (longPress())
				state = NONE; // err
			else
				state = UNPRESSED;

			startTimer(END_INPUT_DELAY); // timer for end of input
			break;

		case UNPRESSED : // nothing happend -> decode
			if (decodeMorse())
				sendStr("\nwrong input\n"); // err
			else
				sendChar(' '); // space between symbols

			inputReset();
			state = NONE;
			break;

		case NONE : // waiting state
		default :
			break;
	}
}

int main(void){

	// initialization

	MCUInit();
	portInit();
	UART0Init();
	LPTMR0Init();

	unsigned int press = 0; // press lock

    while(1){
    	if (!press && !(GPIOE_PDIR & BTN)){ // button press & was not pressed
    		stopTimer();

    		press = 1; // lock input

    		state = PRESSED; // state for interrupt

    		startTimer(END_INPUT_DELAY/4); // timer for long press
    	} else if (press && (GPIOE_PDIR & BTN)) { // button release & was pressed
    		stopTimer(); // stop timer

    		if (state == PRESSED){
    			if (shortPress()) // was pressed not hold
    				state = NONE; // err
    			else
    				state = UNPRESSED;
    		}

    		press = 0; // free lock

    		startTimer(END_INPUT_DELAY); // delay for end of input
    	}
    }
    return 0;
}
////////////////////////////////	 EOF	////////////////////////////////
