/**
 *  subject: ISA
 *  name of the procjet: http nástěnka - server / api
 *  name of the file: api.h
 *  description: library for api.cpp
 *  date: 18.11.2019
 * 
 *  author: xhampl10 (xhampl10@stud.fit.vutbr.cz)
*/

#ifndef API_H
#define API_H

// libraries

#include <string>
#include <iostream>

// namespace

using namespace std;

// structures

struct Node{
    string content;
    Node * next;
};

struct Board{
    string name;
    int index;
    Node * node;
    Board * next;
};

// prototypes

Board * boardFind(string bName);
string boardsList ();
string boardCreate (string bName);
string boardDelete (string bName);
string boardList (string bName);
string itemInsert (string bName, string content);
string itemDelete (string bName, int id);
string itemPut (string bName, int id, string content);
void disposeBoards();
#endif