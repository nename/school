/**
 *  subject: ISA
 *  name of the procjet: http nástěnka - server / api
 *  name of the file: api.cpp
 *  description: main logic for api
 *  date: 18.11.2019
 * 
 *  author: xhampl10 (xhampl10@stud.fit.vutbr.cz)
*/

#include "api.h"

using namespace std;

// globals

Board * board = NULL;
string response;

/**
 * function checks if board already exists
 * 
 * @param string bName board name
 * @return board* temp if board is found, otherwise NULL
 */
Board * boardFind (string bName){

    if (board == NULL) return NULL;

    Board* temp = board;

    while (temp != NULL){
        if (temp->name == bName){
            return temp;
        }
        temp = temp->next;
    }
    
    return NULL;
}

/**
 * function lists all boards (GET /boards)
 * 
 * @return string response response to client
 */
string boardsList (){
    response.clear();

    Board* temp = board;
    string names;

    // goes through all boards
    while (temp != NULL){
        names.append(temp->name);
        names.append("\n");
        temp = temp->next;
    }

    if (names.empty()){
        response.append("HTTP/1.1\t200\tOK\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t0\r\n\r\n");
    } else {
        response.append("HTTP/1.1\t200\tOK\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t");
        response.append(to_string(names.length()));
        response.append("\r\n\r\n");
        response.append(names);
    }

    return response;
}

/**
 * function creates board (POST /boards/name)
 * 
 * @param string bName board name
 * @return string response response to client
 */
string boardCreate (string bName){
    response.clear();

    // checks if board exists
    if (boardFind(bName) != NULL){
        response.append("HTTP/1.1\t409\tBoard Exists\r\n\r\n");
        return response;
    }

    // new node
    Board * newBoard = new Board;

    // check object constructor
    if (newBoard == NULL) { 
        fprintf(stderr, "Error: boardCreate malloc\n");
        response.append("HTTP/1.1\t404\tNot Found\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t40\r\n\r\nBoard couldn't be created. Malloc error.");
        return response;
    }

    // initialization
    newBoard->name = bName; 
    newBoard->index = 0;
    newBoard->node = NULL;
    newBoard->next = NULL;

    // save into list
    if (board == NULL) {
        board = newBoard;
    } else {
        if (board->next == NULL){
            board->next = newBoard;
        } else {
            Board * temp = board;

            while (temp->next != NULL){
                temp = temp->next;
            }

            temp->next = newBoard;
        }
    }
    
    response.append("HTTP/1.1\t201\tOK\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t14\r\n\r\nBoard created.");
    return response;
}

/**
 * function deletes board (DELETE /boards/name)
 * 
 * @param string bName board name
 * @return string response response to client
 */
string boardDelete (string bName) {
    response.clear();

    // check if board already exists
    if (boardFind(bName) == NULL){
        response.append("HTTP/1.1\t404\tNot Found\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t21\r\n\r\nBoard doesn't exist.");
        return response;
    }

    // find board with pointer to it so it could be deleted
    Board * tmp = board;
    Board * tmp2 = NULL;

    while (tmp != NULL){
        if (tmp->name == bName){
            if (tmp2 == NULL){
                board = board->next;
            } else {
                tmp2->next = tmp->next;
            }

            delete tmp;
        }
        tmp2 = tmp;
        tmp = tmp->next;
    }

    response.append("HTTP/1.1\t200\tOK\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t14\r\n\r\nBoard deleted.");
    return response;
}

/**
 * function lists board (GET /board/name)
 * 
 * @param string bName board name
 * @return string response response to client
 */
string boardList (string bName){
    response.clear();

    // checks if board already exists
    Board * temp = boardFind(bName);

    if (temp == NULL){
        response.append("HTTP/1.1\t404\tNot Found\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t16\r\n\r\nBoard not found.");
        return response;
    }

    string cont;

    cont.append("[");
    cont.append(bName);
    cont.append("]\n");

    // checks if board has any items if so, lists them
    if (temp->node != NULL){      
        Node * nd = temp->node;

        for (int i = 1; i <= temp->index; i++){
            cont.append(to_string(i));
            cont.append(". ");
            cont.append(nd->content);
            cont.append("\n");
            nd = nd->next;
        }
    }

    response.append("HTTP/1.1\t200\tOK\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t");
    response.append(to_string(cont.length()));
    response.append("\r\n\r\n");
    response.append(cont);
    response.append("\n");

    return response;
}

/**
 * function inserts item into board (POST /board/name content)
 * 
 * @param string bName board name
 * @param string content content
 * @return string response response to client
 */
string itemInsert (string bName, string content){
    response.clear();

    // checks if board already exists
    Board * temp = boardFind(bName);

    if (temp == NULL){
        response.append("HTTP/1.1\t404\tNot Found\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t16\r\n\r\nBoard not found.");
        return response;
    }

    // creates new node
    Node * newNode = new Node;

    if (newNode == NULL) { 
        fprintf(stderr, "Error: itemInsert malloc\n");
        response.append("HTTP/1.1\t404\tNot Found\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t39\r\n\r\nItem couldn't be created. Malloc error.");
        return response;
    }

    // initialization
    newNode->content = content;
    newNode->next = NULL;

    // puts it into list
    if (temp->node == NULL){
        temp->node = newNode;
    } else if (temp->node->next == NULL){
        temp->node->next = newNode;
    } else {
        Node * tmp = temp->node;
        while (tmp->next != NULL){
            tmp = tmp->next;
        }
        tmp->next = newNode;
    }

    temp->index++;
    response.append("HTTP/1.1\t201\tOK\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t13\r\n\r\nItem created.");
    return response;
}

/**
 * function deletes item from board (DELETE /board/name/id)
 * 
 * @param string bName board name
 * @param int id id of item
 * @return string response response to client
 */
string itemDelete (string bName, int id){
    response.clear();

    // checks if board already exists
    Board * temp = boardFind(bName);

    if (temp == NULL || temp->index < id){
        response.append("HTTP/1.1\t404\tNot Found\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t15\r\n\r\nItem not found.");
        return response;
    }

    // checks for node, if node exists, deletes it
    if (temp->node != NULL){
        Node * tmp = temp->node;
        Node * tmp2 = NULL;
        int i = 1;
        while (i < id){
            tmp2 = tmp;
            tmp = tmp->next;
            i++;
        }
        if (tmp2 == NULL){
            board->node = tmp->next;
        } else {
            tmp2->next = tmp->next;
        }
        delete tmp;
    } else {
        response.append("HTTP/1.1\t404\tNot Found\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t15\r\n\r\nItem not found.");
        return response;
    }
    temp->index--;
    response.append("HTTP/1.1\t200\tOK\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t13\r\n\r\nItem deleted.");
    return response;
}

/**
 * function updates item in board (PUT /board/name/id content)
 * 
 * @param string bName board name
 * @param int id id of item
 * @param string content content
 * @return string response response to client
 */
string itemPut (string bName, int id, string content){
    response.clear();

    // checks if board exists
    Board* temp = boardFind(bName);

    if (temp == NULL || temp->index < id || temp->node == NULL){
        response.append("HTTP/1.1\t404\tNot Found\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t15\r\n\r\nItem not found.");
        return response;
    }

    // finds item to be changed
    Node* tmp = temp->node;
    int i = 1;

    while (i < id){
        tmp = tmp->next;
        i++;
    }
    
    tmp->content = content;
    
    response.append("HTTP/1.1\t200\tOK\r\nContent-Type:\ttext/plain\r\nContent-Lenght:\t17\r\n\r\nItem was updated.");
    return response;
}

/**
 * function clears everything in case of server turn off
 */
void disposeBoards(){
    if (board == NULL) return;

    Board* temp = board;
    Board* tmp = NULL;

    while (temp != NULL){
        if (temp->node != NULL){
            Node* n = temp->node;
            Node* m = NULL;
            while (n != NULL){
                m = n;
                n = n->next;
                delete m;
            }
        }
        tmp = temp;
        temp = temp->next;
        delete tmp;
    }
    board = NULL;
}