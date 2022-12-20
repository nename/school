/**
 *  subject: ISA
 *  name of the procjet: http nástěnka - server
 *  name of the file: isaserver.cpp
 *  description: main logic of server
 *  date: 18.11.2019
 * 
 *  author: xhampl10 (xhampl10@stud.fit.vutbr.cz)
*/

using namespace std;

#include "isaserver.h"
#include "api.h"

/**
 * function handles signals (ctrl + c) and disposes all boards
 * 
 * source: https://stackoverflow.com/questions/1641182/how-can-i-catch-a-ctrl-c-event
 * author: Gab Royer (https://stackoverflow.com/users/126912/gab-royer)
 * edited: auselen (https://stackoverflow.com/users/1163019/auselen)
 * date: 17.11.2019
 * 
 * @param int s signal
*/
void my_handler(int s){
    disposeBoards();
    exit(0);
}

/**
 * function prints error and exits
 * 
 * @param const char* error string to be printed
*/
void printError(const char* error){
    fprintf(stderr, "Error: %s.\n Type -h for help.\n", error);
    exit(1);
}

/**
 * function prints help
*/
void printHelp (){
    fprintf(stderr,"--- HELP ---\nProgram startup: ./isaserver -p <port_number> [-h]\n-p: port number\n-h: help\n");
    exit(0);
}

/**
 * function processes arguments
 * 
 * @param int argc number of arguments
 * @param char** argv program arguments
*/
void processArguments (int argc, char** argv){
    int flag, opt;
    flag = 0;

    while ((opt = getopt(argc, argv, "hp:")) != -1){
        switch (opt){
            case 'h' :
                printHelp();
                break;
            
            case 'p' :
                port = atoi(optarg);
                flag = 1;
                break;

            default :
                printError("wrong argument");
                break;
        }
    }

    if (flag == 0) printError("port not given");
}

/**
 * function checks if header has atribut Content-length and checks if is bigger than BUFFER
 * 
 * @param string request whole packet
 * @return bool false if Content-Length is bigger than BUFFER 
*/
bool contentSizeCheck(string request){
    int size = 0;
    string content;
    
    regex reg ("Content-Length\\s*:*=*\\s*(\\d+)");
    smatch match;
    
    if (regex_search(request, match, reg)){
        size = stoi(match[1]);
        if (size >= BUFFER){
            return false;
        } 
    }
    return true;
}

/**
 * function checks if header has atribut Content-length and if the atribut is 0
 * 
 * @param string request whole packet
 * @return bool false if Content-Length is 0
*/
bool contentSize(string request){
    int size = 0;
    regex reg ("Content-Length\\s*:*=*\\s*(\\d+)");
    smatch match;

    if (regex_search(request, match, reg)){
        size = stoi(match[1]);
        if (size != 0){
            return true;
        }
    }
    return false;
}

/**
 * function processes request, if the packet is correct, calls api
 * 
 * @param string request whole packet
*/
void processRequest (string request){
    resp.clear();
    
    string name, method, url, firstLine, nUrl, content;
    int id = 0;
    regex sp ("\\s+");

    // get first line
    istringstream f(request);
    getline(f, firstLine); 

    // split into method and url
    vector<string> splitLine (sregex_token_iterator(firstLine.begin(), firstLine.end(), sp, -1), sregex_token_iterator()); 
    method = splitLine[0];
    url = splitLine[1];

    // if url isnt /boards splits url into name and url
    if (url != "/boards"){ 
        regex dd ("/");
        vector<string> splitUrl (sregex_token_iterator(url.begin(), url.end(), dd, -1), sregex_token_iterator());
        nUrl = splitUrl[1];
        name = splitUrl[2];

        // if there is id
        if (splitUrl.size() == 4){ 
            id = stoi(splitUrl[3]);
        }

        // split header and content
        regex c ("\r\n\r\n"); 
        vector<string> splitHead (sregex_token_iterator(request.begin(), request.end(), c, -1), sregex_token_iterator()); 
        if (splitHead.size() == 2){
            content = splitHead[1];
        }
    }

    // classification by method and url
    if (method == "GET"){
        if (url == "/boards"){ // GET /boards
            resp = boardsList();
        } else if (nUrl == "board"){
            resp = boardList(name); // GET /board/name
        } else {
            resp = WRONG;
        }
    } else if (method == "POST"){
        if (nUrl == "boards"){ // POST /boards/name
            resp = boardCreate(name);
        } else if (nUrl == "board"){ // POST /board/name content
            if (!contentSize(request) || content.length() == 0){
                resp = WRONG_EMPTY;
                return;
            }
            resp = itemInsert(name, content);
        } else {
            resp = WRONG;
        }
    } else if (method == "DELETE"){
        if (nUrl == "boards"){ // DELETE /boards/name
            resp = boardDelete(name);
        } else if (nUrl == "board"){ // DELETE /board/name/id
            if (id == 0){
                resp = WRONG;
                return;
            }
            resp = itemDelete(name, id);
        } else {
            resp = WRONG;
        }
    } else if (method == "PUT"){
        if (nUrl == "board"){ // PUT /board/name/id content
            if (id == 0){
                resp = WRONG;
                return;
            }
            if (!contentSize(request) || content.length() == 0){
                resp = WRONG_EMPTY;
                return;
            }
            resp = itemPut(name, id, content);
        } else {
            resp = WRONG;
        }
    } else {
        resp = WRONG;
    }
}

/**
 * function starts server
 * 
 * function is based on example from Petr Matoušek
 * source: https://wis.fit.vutbr.cz/FIT/st/cfs.php?file=%2Fcourse%2FISA-IT%2Fexamples&cid=13349 -> examples.zip
 * author: Petr Matoušek
 * date: 10.11.2019
*/
void startServer(){
    int sock, clientSock, len, msgSize, wr;
    struct sockaddr_in server, client;
    string request;
    char buffer[BUFFER];

    // server start up
    if ((sock = socket(AF_INET , SOCK_STREAM , 0)) == -1) printError("socket");

    server.sin_family = AF_INET;
    server.sin_addr.s_addr = INADDR_ANY;
    server.sin_port = htons(port);

    if(bind(sock, (struct sockaddr *) &server, sizeof(server)) < 0) printError("bind");

    if (listen(sock, QUEUE) != 0) printError("listen");

    len = sizeof(client);

    // server run
    while (1){
        request.clear();

        if ((clientSock = accept(sock, (struct sockaddr *) &client, (socklen_t *) &len)) == -1) printError("accept");

        if ((msgSize = read(clientSock, buffer, BUFFER)) > 0){
            request.append(buffer);
            memset(buffer, 0, sizeof(buffer));
        }

        // check if you have everything
        if (!contentSizeCheck(request)){
            if ((msgSize = read(clientSock, buffer, BUFFER)) > 0){
                request.append(buffer);
            }

            if (!contentSizeCheck(request)){
                resp = WRONG;
                wr = write(clientSock, resp.c_str(), resp.length());
                request.clear();
            }
        }
        
        // process request
        if (!request.empty()){
            processRequest(request);

            wr = write(clientSock, resp.c_str(), resp.length());
        }

        close(clientSock);
    }

    close(sock);
}

int main (int argc, char** argv){

    processArguments(argc, argv);

    /**
     * source: https://stackoverflow.com/questions/1641182/how-can-i-catch-a-ctrl-c-event
     * author: Gab Royer (https://stackoverflow.com/users/126912/gab-royer)
     * edited: auselen (https://stackoverflow.com/users/1163019/auselen)
     * date: 17.11.2019
     */
    struct sigaction sigIntHandler;

    sigIntHandler.sa_handler = my_handler;
    sigemptyset(&sigIntHandler.sa_mask);
    sigIntHandler.sa_flags = 0;

    sigaction(SIGINT, &sigIntHandler, NULL);
    // --------------------------------------

    startServer();

    return 0;
}