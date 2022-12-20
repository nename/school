/**
* @file proj2.c
* @brief Druhy projekt z predmetu IOS
* @date 1.5.2018
* @author xhampl10 (xhampl10@stud.fit.vutbr.cz)
*/

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <sys/wait.h>
#include <sys/types.h>
#include <semaphore.h>
#include <fcntl.h>
#include <sys/shm.h>
#include <signal.h>
#include <time.h>
#include <string.h>

// names of semaphores
#define BUS "/xhampl10.ios.bus"
#define MUTEX "/xhampl10.ios.mutex"
#define BOARDED "/xhampl10.ios.board"
#define FINISHED "/xhampl10.ios.finished"
#define ENTRY "/xhampl10.ios.entry"

// semaphores
sem_t *mutex = NULL;
sem_t *bus = NULL;
sem_t *boarded = NULL;
sem_t *finished = NULL;
sem_t *entry = NULL; // write to file

// file
FILE *file;

// shared memory
int *action = NULL;
int actionID = 0;
int *waiting = NULL;
int waitingID = 0;
int *RidCount = NULL;
int RidCountID = 0;
int *TotalRiders = NULL;
int TotalRidersID = 0;
int *Capacity = NULL;
int CapacityID = 0;
int *Finished = NULL;
int FinishedID = 0;
int *Error = NULL;
int ErrorID = 0;

// functions
int init();
void clean();
void BusProcess(int delay);
void Rider(int index);
void GenRiders(int delay, int ammount);

/**
* Function init initialize resources
*
* @return succes or failure
*/
int init (){
  // file
  file = fopen("proj2.out", "w");
  if (file == NULL){ return 1;}

  // semaphores
  if ((mutex = sem_open(MUTEX, O_CREAT | O_EXCL, 0666, 1)) == SEM_FAILED){ return 1;}
  if ((bus = sem_open(BUS, O_CREAT | O_EXCL, 0666, 0)) == SEM_FAILED){ return 1;}
  if ((boarded = sem_open(BOARDED, O_CREAT | O_EXCL, 0666, 0)) == SEM_FAILED){ return 1;}
  if ((finished = sem_open(FINISHED, O_CREAT | O_EXCL, 0666, 0)) == SEM_FAILED){ return 1;}
  if ((entry = sem_open(ENTRY, O_CREAT | O_EXCL, 0666, 1)) == SEM_FAILED){ return 1;}

  // memory
  if ((waitingID = shmget(IPC_PRIVATE, sizeof(int), IPC_CREAT | 0666)) == -1){ return 1;}
  if ((waiting = shmat(waitingID, NULL, 0)) == NULL){return 1;}
  if ((actionID = shmget(IPC_PRIVATE, sizeof(int), IPC_CREAT | 0666)) == -1){ return 1;}
  if ((action = shmat(actionID, NULL, 0)) == NULL){return 1;}
  if ((RidCountID = shmget(IPC_PRIVATE, sizeof(int), IPC_CREAT | 0666)) == -1){ return 1;}
  if ((RidCount = shmat(RidCountID, NULL, 0)) == NULL){return 1;}
  if ((TotalRidersID = shmget(IPC_PRIVATE, sizeof(int), IPC_CREAT | 0666)) == -1){ return 1;}
  if ((TotalRiders = shmat(TotalRidersID, NULL, 0)) == NULL){ return 1;}
  if ((CapacityID = shmget(IPC_PRIVATE, sizeof(int), IPC_CREAT | 0666)) == -1){ return 1;}
  if ((Capacity = shmat(CapacityID, NULL, 0)) == NULL){ return 1;}
  if ((FinishedID = shmget(IPC_PRIVATE, sizeof(int), IPC_CREAT | 0666)) == -1){ return 1;}
  if ((Finished = shmat(FinishedID, NULL, 0)) == NULL){ return 1;}
  if ((ErrorID = shmget(IPC_PRIVATE, sizeof(int), IPC_CREAT | 0666)) == -1){ return 1;}
  if ((Error = shmat(ErrorID, NULL, 0)) == NULL){ return 1;}

  // works
  return 0;
}

/**
* Fuction clean closes, unlinks and deletes everything
*/
void clean(){
  //FILE
  fclose(file);

  //semaphores
  sem_close(mutex);
  sem_close(bus);
  sem_close(boarded);
  sem_close(finished);
  sem_close(entry);

  sem_unlink(MUTEX);
  sem_unlink(BUS);
  sem_unlink(BOARDED);
  sem_unlink(FINISHED);
  sem_unlink(ENTRY);

  //memory
  shmctl(waitingID, IPC_RMID, NULL);
  shmctl(actionID, IPC_RMID, NULL);
  shmctl(RidCountID, IPC_RMID, NULL);
  shmctl(TotalRidersID, IPC_RMID, NULL);
  shmctl(CapacityID, IPC_RMID, NULL);
  shmctl(FinishedID, IPC_RMID, NULL);
  shmctl(ErrorID, IPC_RMID, NULL);
}

/**
* Process bus
*
* @param delay bus sleeps for this time to simulate trip
* @param capacity capacity of bus
*/
void BusProcess (int delay){
  // process error handling
  if ((*Error) == 1){ exit(EXIT_FAILURE);}

  // start up
  sem_wait(entry);
  fprintf(file,"%d\t: BUS  \t: start\n",++(*action));
  sem_post(entry);

  // arrivaal - boarding - depart
  while ((*Finished) < (*TotalRiders-1)){
    // process error handling
    if ((*Error) == 1){ exit(EXIT_FAILURE);}

    // close bus stop
    sem_wait(mutex);
    // process error handling
    if ((*Error) == 1){ exit(EXIT_FAILURE);}

    sem_wait(entry);
    fprintf(file,"%d\t: BUS  \t: arrival\n",++(*action));
    sem_post(entry);
    // check if someone is waiting
    if ((*waiting) > 0){
      sem_wait(entry);
      fprintf(file,"%d\t: BUS  \t: start boarding : %d\n",++(*action),*waiting);
      sem_post(entry);
      // boarding
      sem_post(bus);
      // boarded
      sem_wait(boarded);
      // process error handling
      if ((*Error) == 1){ exit(EXIT_FAILURE);}

      sem_wait(entry);
      fprintf(file,"%d\t: BUS  \t: end boarding : %d\n",++(*action),*waiting);
      sem_post(entry);
    }
    // depart
    sem_wait(entry);
    fprintf(file,"%d\t: BUS  \t: depart\n",++(*action));
    sem_post(entry);
    // open bus stop
    sem_post(mutex);
    // trip s
    if (delay != 0){
      sleep(rand() % delay);
    } else {
      sleep(1);
    }

    sem_wait(entry);
    fprintf(file,"%d\t: BUS  \t: end\n",++(*action));
    sem_post(entry);
    // exit of riders
    while((*RidCount) > 0){
      sem_post(finished);
      (*RidCount)--;
    }
  }
  // end
  sem_wait(entry);
  fprintf(file,"%d\t: BUS  \t: finish\n",++(*action));
  sem_post(entry);

  exit (EXIT_SUCCESS);
}

/**
* Process rider
*
* @param index index of rider
*/
void Rider(int index){
  // process error
  if ((*Error) == 1){ exit(EXIT_FAILURE);}

  sem_wait(entry);
  fprintf(file,"%d\t: RID %d\t: start\n",++(*action),index);
  sem_post(entry);
  // waiting for bus stop to be opened
  sem_wait(mutex);
  // process error
  if ((*Error) == 1){ exit(EXIT_FAILURE);}
  // entering bus stop
  (*waiting)++;

  sem_wait(entry);
  fprintf(file,"%d\t: RID %d\t: enter: %d\n",++(*action),index,*waiting);
  sem_post(entry);
  // for other waiting people
  sem_post(mutex);
  // waiting for bus
  sem_wait(bus);
  // process error
  if ((*Error) == 1){ exit(EXIT_FAILURE);}
  // boarding
  sem_wait(entry);
  fprintf(file,"%d\t: RID %d\t: boarding\n",++(*action),index);
  sem_post(entry);
  (*waiting)--;
  (*RidCount)++;
  // check if is someone waiting or bus is full
  if ((*waiting) == 0 || (*RidCount) == (*Capacity)){
    // full or nobody waiting
    sem_post(boarded);
  } else {
    // boarding
    sem_post(bus);
  }
  // waiting for bus to finish trip
  sem_wait(finished);
  // process error
  if ((*Error) == 1){ exit(EXIT_FAILURE);}

  sem_wait(entry);
  fprintf(file,"%d\t: RID %d\t: finish\n",++(*action),index);
  sem_post(entry);
  // counter for cycle
  (*Finished)++;

  exit (EXIT_SUCCESS);
}

/**
* Function generates riders.
*
* @param delay generator sleeps for this time
* @param ammount number of riders to be generated
*/
void GenRiders (int delay, int ammount){

  for (int i = 1; i <= ammount; i++){
    pid_t rid = fork();
    if (rid == 0){ // child
      Rider(i);
    } else if (rid < 0){
      // process controler set
      *Error = 1;
      // open semaphores so processes can run and exit
      sem_post(mutex);
      sem_post(mutex);
      sem_post(bus);
      sem_post(finished);
      sem_post(boarded);
      // ends generator
      exit (EXIT_FAILURE);
    }
    // delay
    if (delay != 0){
      sleep(rand() % delay);
    } else {
      sleep(1);
    }
  }
  // waiting for childs to exit
  for (int i = 1; i <= ammount; i++){
    wait(NULL);
  }
  exit (EXIT_SUCCESS);
}

int main (int argc, char *argv[]){
  if (argc != 5){
    fprintf(stderr, "wrong number of arguments\n");
    return 1;
  } else if (atoi(argv[1]) < 0){
    fprintf(stderr, "Arg1 is number of riders and has to be bigger than 0!\n");
    return 1;
  } else if (atoi(argv[2]) < 0){
    fprintf(stderr, "Arg2 is capacity of bus and has to be bigger than 0!\n");
    return 1;
  } else if(atoi(argv[3]) < 0 || atoi(argv[3]) > 1000){
    fprintf(stderr, "Arg3 is delay before generating riders and has to be within (0,1000)!\n");
    return 1;
  } else if(atoi(argv[4]) < 0 || atoi(argv[4]) > 1000){
    fprintf(stderr, "Arg4 is delay that simualtes trip and has to be within (0,1000)!\n");
    return 1;
  }

  srand(time(NULL));

  // initialize
  if (init() == 1){
    fprintf(stderr, "Error in initialization! Should be cleaned. Rerun!\n");
    clean();
    return 1;
  }

  // set buffering
  setbuf(file, NULL);

  // values
  *TotalRiders = atoi(argv[1]);
  *Capacity = atoi(argv[2]);
  int RidDelay = atoi(argv[3]);
  int BusDelay = atoi(argv[4]);

  pid_t generator = fork();

  if (generator == 0){ // child
    GenRiders(RidDelay, (*TotalRiders));
  } else if (generator < 0){ // error handling
    fprintf(stderr, "Error in generator process.\n");
    clean();
    return 1;
  } else {

    pid_t bus = fork();

    if (bus == 0){ // child
      BusProcess(BusDelay);
    } else if(bus < 0){ // error handling
      fprintf(stderr, "Error in bus process.\n");
      clean();
      return 1;
    }
  }
  // waiting for children
  wait(NULL);
  wait(NULL);
  // process error handling
  if ((*Error) == 1){
    fprintf(stderr, "Error in generating process.\n");
    clean();
    return 1;
  }

  // clean and end
  clean();
  return 0;
}
