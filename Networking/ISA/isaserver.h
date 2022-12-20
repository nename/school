/**
 *  subject: ISA
 *  name of the procjet: http nástěnka - server
 *  name of the file: isaserver.h
 *  description: library for isaserver.cpp
 *  date: 18.11.2019
 * 
 *  author: xhampl10 (xhampl10@stud.fit.vutbr.cz)
*/

#ifndef ISASERVER_H
#define ISASERVER_H

// libraries

#include <sys/socket.h>
#include <arpa/inet.h>
#include <unistd.h> 
#include <stdlib.h> 
#include <netinet/in.h> 
#include <sys/types.h>
#include <vector>
#include <iostream>
#include <string>
#include <regex>
#include <sstream>
#include <signal.h>

// constants

#define BUFFER 1024
#define QUEUE 1
#define WRONG "HTTP/1.1\t404\tNot Found\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t16\r\n\r\nUnknown request."
#define WRONG_EMPTY "HTTP/1.1\t400\tNot Found\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t17\r\n\r\nContent length 0."

// globals

int port;
string resp;

// prototypes

void printError (const char* error);
void printHelp ();
void processArguments (int argc, char** argv);
bool contentSizeCheck(string header);
void processRequest(string request);
void startServer();
#endif