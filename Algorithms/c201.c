
/* c201.c *********************************************************************}
{* Téma: Jednosměrný lineární seznam
**
**                     Návrh a referenční implementace: Petr Přikryl, říjen 1994
**                                          Úpravy: Andrea Němcová listopad 1996
**                                                   Petr Přikryl, listopad 1997
**                                Přepracované zadání: Petr Přikryl, březen 1998
**                                  Přepis do jazyka C: Martin Tuček, říjen 2004
**                                            Úpravy: Kamil Jeřábek, září 2018
**
** Implementujte abstraktní datový typ jednosměrný lineární seznam.
** Užitečným obsahem prvku seznamu je celé číslo typu int.
** Seznam bude jako datová abstrakce reprezentován proměnnou typu tList.
** Definici konstant a typů naleznete v hlavičkovém souboru c201.h.
**
** Vaším úkolem je implementovat následující operace, které spolu s výše
** uvedenou datovou částí abstrakce tvoří abstraktní datový typ tList:
**
**      InitList ...... inicializace seznamu před prvním použitím,
**      DisposeList ... zrušení všech prvků seznamu,
**      InsertFirst ... vložení prvku na začátek seznamu,
**      First ......... nastavení aktivity na první prvek,
**      CopyFirst ..... vrací hodnotu prvního prvku,
**      DeleteFirst ... zruší první prvek seznamu,
**      PostDelete .... ruší prvek za aktivním prvkem,
**      PostInsert .... vloží nový prvek za aktivní prvek seznamu,
**      Copy .......... vrací hodnotu aktivního prvku,
**      Actualize ..... přepíše obsah aktivního prvku novou hodnotou,
**      Succ .......... posune aktivitu na další prvek seznamu,
**      Active ........ zjišťuje aktivitu seznamu.
**
** Při implementaci funkcí nevolejte žádnou z funkcí implementovaných v rámci
** tohoto příkladu, není-li u dané funkce explicitně uvedeno něco jiného.
**
** Nemusíte ošetřovat situaci, kdy místo legálního ukazatele na seznam předá
** někdo jako parametr hodnotu NULL.
**
** Svou implementaci vhodně komentujte!
**
** Terminologická poznámka: Jazyk C nepoužívá pojem procedura.
** Proto zde používáme pojem funkce i pro operace, které by byly
** v algoritmickém jazyce Pascalovského typu implemenovány jako
** procedury (v jazyce C procedurám odpovídají funkce vracející typ void).
**/

#include "c201.h"

int errflg;
int solved;

void Error() {
/*
** Vytiskne upozornění na to, že došlo k chybě.
** Tato funkce bude volána z některých dále implementovaných operací.
**/
    printf ("*ERROR* The program has performed an illegal operation.\n");
    errflg = TRUE;                      /* globální proměnná -- příznak chyby */
}

void InitList (tList *L) {
/*
** Provede inicializaci seznamu L před jeho prvním použitím (tzn. žádná
** z následujících funkcí nebude volána nad neinicializovaným seznamem).
** Tato inicializace se nikdy nebude provádět nad již inicializovaným
** seznamem, a proto tuto možnost neošetřujte. Vždy předpokládejte,
** že neinicializované proměnné mají nedefinovanou hodnotu.
**/

  L->Act = NULL; // initialize
  L->First = NULL;
}

void DisposeList (tList *L) {
/*
** Zruší všechny prvky seznamu L a uvede seznam L do stavu, v jakém se nacházel
** po inicializaci. Veškerá paměť používaná prvky seznamu L bude korektně
** uvolněna voláním operace free.
***/
  tElemPtr temp = NULL; // temporary element for freeing

  while (L->First != NULL){

    temp = L->First; // saves first element of the list

    L->First = L->First->ptr; // saves next element as first

    free(temp);   // frees saved element
  }
  L->First = NULL;
  L->Act = NULL;
}

void InsertFirst (tList *L, int val) {
/*
** Vloží prvek s hodnotou val na začátek seznamu L.
** V případě, že není dostatek paměti pro nový prvek při operaci malloc,
** volá funkci Error().
**/

  tElemPtr temp = NULL;

  if ((temp = malloc(sizeof(struct tElem))) == NULL){ // checks if alocation goes through
    Error();
    return;
  }

  temp->data = val; // saves value into temporary element

  if (L->First == NULL){ // if the list is empty saves element as first
    temp->ptr = NULL;
  } else {
    temp->ptr = L->First; // if the list is not empty and sets pointer
  }

  L->First = temp;
}

void First (tList *L) {
/*
** Nastaví aktivitu seznamu L na jeho první prvek.
** Funkci implementujte jako jediný příkaz, aniž byste testovali,
** zda je seznam L prázdný.
**/

  L->Act = L->First; // changes active element to first element
}

void CopyFirst (tList *L, int *val) {
/*
** Prostřednictvím parametru val vrátí hodnotu prvního prvku seznamu L.
** Pokud je seznam L prázdný, volá funkci Error().
**/

  if (L->First == NULL){ // check if the list is empty
    Error();
    return;
  } else {
    *val = L->First->data; // saves value from first element to val
  }
}

void DeleteFirst (tList *L) {
/*
** Zruší první prvek seznamu L a uvolní jím používanou paměť.
** Pokud byl rušený prvek aktivní, aktivita seznamu se ztrácí.
** Pokud byl seznam L prázdný, nic se neděje.
**/

  if (L->First == NULL){ // check if the list is empty
    return;
  }

  if (L->Act == L->First){ // if active element is first element in the list
    L->Act = NULL; // active element is lost
  }

  tElemPtr temp = L->First; // saves element
  L->First = L->First->ptr; // saves next element as first
  free(temp); // deletes saved element
}

void PostDelete (tList *L) {
/*
** Zruší prvek seznamu L za aktivním prvkem a uvolní jím používanou paměť.
** Pokud není seznam L aktivní nebo pokud je aktivní poslední prvek seznamu L,
** nic se neděje.
**/

  if (L->Act == NULL || L->Act->ptr == NULL){ // if the list isn't active or the actove element is last
    return;
  } else {
    tElemPtr temp = L->Act->ptr; // saves element that should be removed

    if (temp->ptr == NULL){ // checks if the next element after selected is NULL
      L->Act->ptr = NULL;
    } else{
      L->Act->ptr = temp->ptr;
    }

    free(temp);
  }
}

void PostInsert (tList *L, int val) {
/*
** Vloží prvek s hodnotou val za aktivní prvek seznamu L.
** Pokud nebyl seznam L aktivní, nic se neděje!
** V případě, že není dostatek paměti pro nový prvek při operaci malloc,
** zavolá funkci Error().
**/
  tElemPtr temp = NULL;

  if ((temp = malloc(sizeof(struct tElem))) == NULL) { // checks if alocation goes through
    Error();
    return;
  }

  if (L->Act == NULL){ // if the list isn't active
    return;
  }

  temp->data = val;

  if(L->Act->ptr == NULL){ // if the active element is last
    temp->ptr = NULL;
  } else {
    temp->ptr = L->Act->ptr;
  }

  L->Act->ptr = temp;
}

void Copy (tList *L, int *val) {
/*
** Prostřednictvím parametru val vrátí hodnotu aktivního prvku seznamu L.
** Pokud seznam není aktivní, zavolá funkci Error().
**/

  if (L->Act == NULL){ // if the list isnt active
    Error();
    return;
  } else {
    *val = L->Act->data; // saves value of active element
    return;
  }
}

void Actualize (tList *L, int val) {
/*
** Přepíše data aktivního prvku seznamu L hodnotou val.
** Pokud seznam L není aktivní, nedělá nic!
**/

  if (L->Act == NULL){ // checks if the list is active
    return;
  } else {
    L->Act->data = val; // rewrites value of active element
    return;
  }
}

void Succ (tList *L) {
/*
** Posune aktivitu na následující prvek seznamu L.
** Všimněte si, že touto operací se může aktivní seznam stát neaktivním.
** Pokud není předaný seznam L aktivní, nedělá funkce nic.
**/

  if (L->Act == NULL){ // checks if the list is active
    return;
  } else {
    if (L->Act->ptr == NULL){ // checks if the active element isn't last
      L->Act = NULL;
    } else {
      L->Act = L->Act->ptr; // marks next element as active
    }
  }
}

int Active (tList *L) {
/*
** Je-li seznam L aktivní, vrací nenulovou hodnotu, jinak vrací 0.
** Tuto funkci je vhodné implementovat jedním příkazem return.
**/

  return ((L->Act == NULL) ? 0 : 1); // checks if the list is active
}

/* Konec c201.c */
