/**
 *  subject: ISA
 *  name of the procjet: http nástěnka - klient
 *  name of the file: isaclient.h
 *  description: library for isaclient.cpp
 *  date: 18.11.2019
 * 
 *  author: xhampl10 (xhampl10@stud.fit.vutbr.cz)
*/

#ifndef ISACLIENT_H
#define ISACLIENT_H

// libraries

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <string>
#include <regex>
#include <vector>
#include <sys/socket.h>
#include <unistd.h>
#include<netdb.h>
#include <netinet/in.h>
#include <arpa/inet.h>

// name space

using namespace std;

// constatns

#define BUFFER 4096

// globals

int port;
char* host;
string request;
string response;

// prototypes

void printError(const char* str);
void printHelp ();
void processArguments (int argc, char** argv);
void sendMessage();
void printResponse();
#endif