
/* c206.c **********************************************************}
{* Téma: Dvousměrně vázaný lineární seznam
**
**                   Návrh a referenční implementace: Bohuslav Křena, říjen 2001
**                            Přepracované do jazyka C: Martin Tuček, říjen 2004
**                                            Úpravy: Kamil Jeřábek, září 2018
**
** Implementujte abstraktní datový typ dvousměrně vázaný lineární seznam.
** Užitečným obsahem prvku seznamu je hodnota typu int.
** Seznam bude jako datová abstrakce reprezentován proměnnou
** typu tDLList (DL znamená Double-Linked a slouží pro odlišení
** jmen konstant, typů a funkcí od jmen u jednosměrně vázaného lineárního
** seznamu). Definici konstant a typů naleznete v hlavičkovém souboru c206.h.
**
** Vaším úkolem je implementovat následující operace, které spolu
** s výše uvedenou datovou částí abstrakce tvoří abstraktní datový typ
** obousměrně vázaný lineární seznam:
**
**      DLInitList ...... inicializace seznamu před prvním použitím,
**      DLDisposeList ... zrušení všech prvků seznamu,
**      DLInsertFirst ... vložení prvku na začátek seznamu,
**      DLInsertLast .... vložení prvku na konec seznamu,
**      DLFirst ......... nastavení aktivity na první prvek,
**      DLLast .......... nastavení aktivity na poslední prvek,
**      DLCopyFirst ..... vrací hodnotu prvního prvku,
**      DLCopyLast ...... vrací hodnotu posledního prvku,
**      DLDeleteFirst ... zruší první prvek seznamu,
**      DLDeleteLast .... zruší poslední prvek seznamu,
**      DLPostDelete .... ruší prvek za aktivním prvkem,
**      DLPreDelete ..... ruší prvek před aktivním prvkem,
**      DLPostInsert .... vloží nový prvek za aktivní prvek seznamu,
**      DLPreInsert ..... vloží nový prvek před aktivní prvek seznamu,
**      DLCopy .......... vrací hodnotu aktivního prvku,
**      DLActualize ..... přepíše obsah aktivního prvku novou hodnotou,
**      DLSucc .......... posune aktivitu na další prvek seznamu,
**      DLPred .......... posune aktivitu na předchozí prvek seznamu,
**      DLActive ........ zjišťuje aktivitu seznamu.
**
** Při implementaci jednotlivých funkcí nevolejte žádnou z funkcí
** implementovaných v rámci tohoto příkladu, není-li u funkce
** explicitně uvedeno něco jiného.
**
** Nemusíte ošetřovat situaci, kdy místo legálního ukazatele na seznam
** předá někdo jako parametr hodnotu NULL.
**
** Svou implementaci vhodně komentujte!
**
** Terminologická poznámka: Jazyk C nepoužívá pojem procedura.
** Proto zde používáme pojem funkce i pro operace, které by byly
** v algoritmickém jazyce Pascalovského typu implemenovány jako
** procedury (v jazyce C procedurám odpovídají funkce vracející typ void).
**/

#include "c206.h"

int errflg;
int solved;

void DLError() {
/*
** Vytiskne upozornění na to, že došlo k chybě.
** Tato funkce bude volána z některých dále implementovaných operací.
**/
  printf ("*ERROR* The program has performed an illegal operation.\n");
  errflg = TRUE;             /* globální proměnná -- příznak ošetření chyby */
  return;
}

void DLInitList (tDLList *L) {
/*
** Provede inicializaci seznamu L před jeho prvním použitím (tzn. žádná
** z následujících funkcí nebude volána nad neinicializovaným seznamem).
** Tato inicializace se nikdy nebude provádět nad již inicializovaným
** seznamem, a proto tuto možnost neošetřujte. Vždy předpokládejte,
** že neinicializované proměnné mají nedefinovanou hodnotu.
**/

  L->First = NULL; // initialize
  L->Act = NULL;
  L->Last = NULL;
}

void DLDisposeList (tDLList *L) {
/*
** Zruší všechny prvky seznamu L a uvede seznam do stavu, v jakém
** se nacházel po inicializaci. Rušené prvky seznamu budou korektně
** uvolněny voláním operace free.
**/

  tDLElemPtr temp = NULL;

  while(L->First != NULL){
    temp = L->First;
    L->First = L->First->rptr;

    free(temp);
  }
  L->First = NULL;
  L->Act = NULL;
  L->Last = NULL;
}

void DLInsertFirst (tDLList *L, int val) {
/*
** Vloží nový prvek na začátek seznamu L.
** V případě, že není dostatek paměti pro nový prvek při operaci malloc,
** volá funkci DLError().
**/

  tDLElemPtr temp = NULL;

  if ((temp = malloc(sizeof(struct tDLElem))) == NULL){ // checks if alocation goes through
    DLError();
    return;
  } else {
    temp->data = val; // saves data and sets pointers
    temp->lptr = NULL;
    temp->rptr = L->First;

    if (L->First == NULL){ // checks if the list is empty to set pointers
      L->Last = temp;
    } else {
      L->First->lptr = temp;
    }

    L->First = temp;
  }
}

void DLInsertLast(tDLList *L, int val) {
/*
** Vloží nový prvek na konec seznamu L (symetrická operace k DLInsertFirst).
** V případě, že není dostatek paměti pro nový prvek při operaci malloc,
** volá funkci DLError().
**/

  tDLElemPtr temp = NULL;

  if ((temp = malloc(sizeof(struct tDLElem))) == NULL){ // checks if alocation goes through
    DLError();
    return;
  } else {
    temp->data = val; // sets values and pointers
    temp->lptr = L->Last;
    temp->rptr = NULL;

    if (L->First == NULL){ // checks if the list is empty
      L->First = temp;
    } else {
      L->Last->rptr = temp;
    }

    L->Last = temp;
  }
}

void DLFirst (tDLList *L) {
/*
** Nastaví aktivitu na první prvek seznamu L.
** Funkci implementujte jako jediný příkaz (nepočítáme-li return),
** aniž byste testovali, zda je seznam L prázdný.
**/

  L->Act = L->First; // sets first element as active element
}

void DLLast (tDLList *L) {
/*
** Nastaví aktivitu na poslední prvek seznamu L.
** Funkci implementujte jako jediný příkaz (nepočítáme-li return),
** aniž byste testovali, zda je seznam L prázdný.
**/

  L->Act = L->Last; // sets last element as active element
}

void DLCopyFirst (tDLList *L, int *val) {
/*
** Prostřednictvím parametru val vrátí hodnotu prvního prvku seznamu L.
** Pokud je seznam L prázdný, volá funkci DLError().
**/

  if (L->First == NULL){ // checks if the list is empty
    DLError();
    return;
  } else {
    *val = L->First->data; // saves value of first element
  }
}

void DLCopyLast (tDLList *L, int *val) {
/*
** Prostřednictvím parametru val vrátí hodnotu posledního prvku seznamu L.
** Pokud je seznam L prázdný, volá funkci DLError().
**/

  if (L->Last == NULL){ // checks if the list is empty
    DLError();
    return;
  } else {
    *val = L->Last->data; // saves value of last element
  }
}

void DLDeleteFirst (tDLList *L) {
/*
** Zruší první prvek seznamu L. Pokud byl první prvek aktivní, aktivita
** se ztrácí. Pokud byl seznam L prázdný, nic se neděje.
**/

  if (L->First == NULL){ // checks if the list is empty
    return;
  }

  tDLElemPtr temp = L->First; // saves element to be deleted

  if (L->Act == L->First){ // checsk if first element is first element -> loses active element
    L->Act = NULL;
  }

  L->First = L->First->rptr;  // saves next element
  L->First->lptr = NULL; // deletes pointers

  if (temp->rptr == NULL){
    L->Last = L->First;
  }
  free(temp);
}

void DLDeleteLast (tDLList *L) {
/*
** Zruší poslední prvek seznamu L. Pokud byl poslední prvek aktivní,
** aktivita seznamu se ztrácí. Pokud byl seznam L prázdný, nic se neděje.
**/
  if (L->First == NULL){ // checks if the list is empty
    return;
  }

  if (L->Act == L->Last){ // checks if the last element is first element -> loses activity
    L->Act = NULL;
  }

  tDLElemPtr temp = L->Last; // saves element to be deleted

  L->Last = L->Last->lptr; // saves next element
  free(temp);

  if (L->Last == NULL){
    L->First = NULL;
  } else {
    L->Last->rptr = NULL; // deletes pointers
  }
}

void DLPostDelete (tDLList *L) {
/*
** Zruší prvek seznamu L za aktivním prvkem.
** Pokud je seznam L neaktivní nebo pokud je aktivní prvek
** posledním prvkem seznamu, nic se neděje.
**/

  if ( L->Act == L->Last || L->Act == NULL){ // checks if the last element is active or if the list isn't active
    return;
  } else {
    tDLElemPtr temp = L->Act->rptr;

    if (temp->rptr == NULL){ // if the next element after one being deleted is null
      L->Act->rptr = NULL;
      L->Last = L->Act;
    } else {
      L->Act->rptr = temp->rptr;
  		temp->rptr->lptr = L->Act;
    }
    free(temp);
  }
}

void DLPreDelete (tDLList *L) {
/*
** Zruší prvek před aktivním prvkem seznamu L .
** Pokud je seznam L neaktivní nebo pokud je aktivní prvek
** prvním prvkem seznamu, nic se neděje.
**/

  if (L->Act == L->First || L->Act == NULL){ // checks if the first element is active or if the list isn't active
    return;
  } else {
    tDLElemPtr temp = L->Act->lptr;

    if (temp->lptr == NULL){ // checks if the element before one being deleted exists
      L->Act->lptr = NULL;
      L->First = L->Act;
    } else {
      L->Act->lptr = temp->lptr;
  		temp->lptr->rptr = L->Act;
    }
    free(temp);
  }
}

void DLPostInsert (tDLList *L, int val) {
/*
** Vloží prvek za aktivní prvek seznamu L.
** Pokud nebyl seznam L aktivní, nic se neděje.
** V případě, že není dostatek paměti pro nový prvek při operaci malloc,
** volá funkci DLError().
**/

  tDLElemPtr temp = NULL;

  if ((temp = malloc(sizeof(struct tDLElem))) == NULL){ // checks if the alocation goes through
    DLError();
    return;
  } else if (L->Act == NULL){ // checks if the list is active
    return;
  } else {
    temp->data = val;
    temp->lptr = L->Act;
    temp->rptr = NULL;

    if (L->Act->rptr == NULL){ // checks if next element from active element exists
      L->Last = temp;
    } else {
      temp->rptr = L->Act->rptr;
      L->Act->rptr->lptr = temp;
    }

    L->Act->rptr = temp;
  }
}

void DLPreInsert (tDLList *L, int val) {
/*
** Vloží prvek před aktivní prvek seznamu L.
** Pokud nebyl seznam L aktivní, nic se neděje.
** V případě, že není dostatek paměti pro nový prvek při operaci malloc,
** volá funkci DLError().
**/

  tDLElemPtr temp = NULL;

  if ((temp = malloc(sizeof(struct tDLElem))) == NULL){ // checks if alocation goes through
    DLError();
    return;
  } else if( L->Act == NULL){ // checks if the list is active
    return;
  } else {
    temp->data = val;
    temp->rptr = L->Act;
    temp->lptr = NULL;
    if (L->Act->lptr == NULL){ // checks if the element behind active element exists
      L->First = temp;
    } else {
      temp->lptr = L->Act->lptr;
      L->Act->lptr->rptr = temp;
    }
    L->Act->lptr = temp;
  }
}

void DLCopy (tDLList *L, int *val) {
/*
** Prostřednictvím parametru val vrátí hodnotu aktivního prvku seznamu L.
** Pokud seznam L není aktivní, volá funkci DLError ().
**/

  if (L->Act == NULL){ // checks if the list is active
    DLError();
    return;
  } else {
    *val = L->Act->data; // saves value of active element
  }
}

void DLActualize (tDLList *L, int val) {
/*
** Přepíše obsah aktivního prvku seznamu L.
** Pokud seznam L není aktivní, nedělá nic.
**/

  if (L->Act == NULL){ // checks if the list is active
    return;
  } else {
    L->Act->data = val; // rewrites value of active element
  }
}

void DLSucc (tDLList *L) {
/*
** Posune aktivitu na následující prvek seznamu L.
** Není-li seznam aktivní, nedělá nic.
** Všimněte si, že při aktivitě na posledním prvku se seznam stane neaktivním.
**/

  if (L->Act == NULL){ // checks if the list is acitve
    return;
  } else {
    L->Act = L->Act->rptr;
  }
}

void DLPred (tDLList *L) {
/*
** Posune aktivitu na předchozí prvek seznamu L.
** Není-li seznam aktivní, nedělá nic.
** Všimněte si, že při aktivitě na prvním prvku se seznam stane neaktivním.
**/

  if (L->Act == NULL){ // checks if the list is active
    return;
  } else {
    L->Act = L->Act->lptr;
  }
}

int DLActive (tDLList *L) {
/*
** Je-li seznam L aktivní, vrací nenulovou hodnotu, jinak vrací 0.
** Funkci je vhodné implementovat jedním příkazem return.
**/

  return (L->Act == NULL ? 0 : 1); // checks if the list is active, returns 1 else 0
}

/* Konec c206.c*/
