/**
 *  subject: ISA
 *  name of the procjet: http nástěnka - klient
 *  name of the file: isaclient.cpp
 *  description: main logic for client
 *  date: 18.11.2019
 * 
 *  author: xhampl10 (xhampl10@stud.fit.vutbr.cz)
*/

#include "isaclient.h"

using namespace std;

/**
 * function prints error and exits
 * 
 * @param const char* error string to be printed
*/
void printError(const char* str){
    fprintf(stderr, "Error %s. Run program with argument -h for help.\n", str);
    exit(1);
}

/**
 * function prints help
*/
void printHelp (){
    fprintf(stderr,"--- HELP ---\nprogram run: ./isaclient [-h] -H <host> -p <port> <command>\n-h: help\n-H: host\n-p: port\nExamples in readme or documentation.\n");
    exit(0);
}

/**
 * function processes arguments
 * 
 * @param int argc number of arguments
 * @param char** argv program arguments
*/
void processArguments (int argc, char** argv){
    int flagPort, flagHost, flagID, flagCt;
    string content;

    flagPort = flagHost = flagID = flagCt = 0;
    request = "";
    content = "";

    if (argc != 2 && (argc < 6 || argc > 10)){ // check # of arguments
        printError("number of arguments");
    }

    for (int i = 1; i < argc; i++){
        if (strcmp(argv[i], "-h") == 0){ // help
            printHelp();
        } else if (strcmp(argv[i], "-H") == 0){ // host
            if ((argc - i) < 2) printError("number of arguments");

            flagHost = 1;
            host = argv[++i];
        } else if (strcmp(argv[i], "-p") == 0){ // port
            if ((argc - i) < 2) printError("number of arguments");

            flagPort = 1;
            string po;
            po.append(argv[++i]);
            smatch match;
            regex r ("(\\d+)");

            if (regex_match(po, match, r)){
                port = stoi(po);
            } else {
                printError("wrong port argument");
            }

            if (port == 0) printError("wrong port argument");
        } else if ((strcmp(argv[i], "boards") == 0)){ // /boards
            request.append("GET\t/boards");
        } else if ((strcmp(argv[i], "board") == 0)){ // /board/
            if ((argc - i) != 3) printError("number of arguments");

            if ((strcmp(argv[i+1], "add") == 0)){ // post /board/name
                request.append("POST\t/boards/");
            } else if ((strcmp(argv[i+1], "delete") == 0)){ // delete /board/name
                request.append("DELETE\t/boards/");
            } else if ((strcmp(argv[i+1], "list") == 0)){ // get /board/name
                request.append("GET\t/board/");
            } else {
                printError("wrong arguments");
            }

            request.append(argv[i+2]);
        } else if ((strcmp(argv[i], "item") == 0)){ // /item/
            if ((strcmp(argv[i+1], "add") == 0)){ // post /item/name content
                if ((argc - i) != 4) printError("number of arguments");

                request.append("POST\t/board/");
                content.append(argv[i+3]);
                flagCt = 1;
            } else if ((strcmp(argv[i+1], "delete") == 0)){ // delete /item/name/id
                if ((argc - i) != 4) printError("number of arguments");

                request.append("DELETE\t/board/");
                flagID = 1;
            } else if ((strcmp(argv[i+1], "update") == 0)){ // put /item/name/id content
                if ((argc - i) != 5) printError("number of arguments");

                request.append("PUT\t/board/");
                content.append(argv[i+4]);
                flagID = flagCt = 1;
            } else {
                printError("wrong arguments");
            }

            request.append(argv[i+2]);

            if (flagID){
                request.append("/");
                request.append(argv[i+3]);
            }
        }
    }

    if (flagHost != 1 || flagPort != 1) printError("missing arguments");

    request.append("\tHTTP/1.1\r\nHost:");
    request.append(host);
    
    if (flagCt){
        regex r ("[a-zA-Z0-9]*\\s*");
        if (!regex_match(content,r)){
            printError("content [a-zA-Z0-9]");
        }
        request.append("\r\nContent-Type:text/plain\r\nContent-Length:");
        request.append(to_string(content.size()));
        request.append("\r\n\r\n");
        request.append(content);
    } else {
        request.append("\r\n\r\n");
    }
}

/**
 * function sends message to host
 * 
 * function is based on example from Petr Matoušek
 * source: https://wis.fit.vutbr.cz/FIT/st/cfs.php?file=%2Fcourse%2FISA-IT%2Fexamples&cid=13349 -> examples.zip
 * author: Petr Matoušek
 * date: 10.11.2019
*/
void sendMessage(){
    int soc, msg_size, wr;
    socklen_t len;
    struct sockaddr_in client, server;
    struct hostent *ip;
    char buffer[BUFFER];
    int i;

    // socket setup
    memset(&server, 0, sizeof(server));
    memset(&client, 0, sizeof(client));
    len = sizeof(client);

    if ((ip = gethostbyname(host)) == NULL){
        printError("gethostbyname");
    }

    server.sin_family = AF_INET;
    memcpy(&server.sin_addr, ip->h_addr, ip->h_length);
    server.sin_port = htons(port);

    if ((soc = socket(AF_INET, SOCK_STREAM, 0)) == -1){
        printError("socket");
    }

    if (connect(soc, (struct sockaddr*) &server, sizeof(server)) == -1){
        printError("connect");
    }

    if (getsockname(soc, (struct sockaddr*) &client, &len) == -1){
        printError("getsockname");
    }
    
    // send message
    wr = write(soc, request.c_str(), request.length());
    if (wr == -1){
        printError("write");
    }

    // read response
    if ((i = read(soc, buffer, BUFFER)) == -1){
        printError("read");
    } else {
        response.append(buffer);
    }

    close(soc);
}
/**
 * functions prints response from server
 */
void printResponse(){
    string content, header;
    regex c ("\r\n\r\n");

    vector<string> splitHead (sregex_token_iterator(response.begin(), response.end(), c, -1), sregex_token_iterator());
    header = splitHead[0];

    if (splitHead.size() == 2){
        content = splitHead[1];
    }

    fprintf(stderr, "%s\n", header.c_str());
    fprintf(stdout, "%s\n", content.c_str());
}

int main (int argc, char** argv){
    
    processArguments(argc, argv);

    sendMessage();

    printResponse();

    return 0;
}