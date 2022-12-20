# IMP Projekt - dekodér Morseovy abecedy
Tento projekt se zaobírá implementování dekodéru Morseovy abecedy na platformě FITkit 3.0. Smyslem této práce je detekování délky stlačení tlačítka na kitu, následné dekódování a odeslání dekódovaného znaku přes UART.
## Ovládání
Tlačítko SW5 (horní tlačítko z uspořádaných tlačítek do tvaru šipek) slouží k zadávání kódu Morseovy abecedy. Krátké stlačení představuje tečku a dlouhé pomlčku. Po každém zadání znaku se přehraje zvuk přes reproduktor, krátký v případě tečky a dlouhý v případě pomlčky. Následné nezmáčknutí tlačítka způsobí dekódování znaku a poslání dekódovaného znaku přes UART.
## Způsob řešení
Program čeká na zmáčknutí tlačítka. Následně spustí časovač a nastaví stav pro rozhodnutí v přerušení. Pokud dojde k puštění tlačítka, zkontroluje se, zda odpovídá stav prvotnímu stlačení a zda již nebyl vyřešen a následně se zavolá funkce _shortPress(void)_, která zkontroluje pozici zadávaného znaku v poli pro vstup a přidá tečku. Následně zapne časovač pro konec zadávání a vynuluje stav pro přerušení. Pokud nedojde k puštění tlačítka, vyvolá se přerušení časovačem, které zavolá funkci _longPress(void)_, která zkontroluje pozici zadávaného znaku a přidá pomlčku, spustí časovač pro konec zadávání a vymaže stav. V případě, že časovač vyvolá přerušení, kdy došlo ke zmáčknutí tlačítka a poté v daném intervalu nedošlo k dalšímu zmáčknutí, začne dekódovat vstup.
## Implementační detaily
### Časovač
Program používá časovač _LPTMR_. Časovač se spouští jen v případě stlačení tlačítka.  Využívá funkcí _startTimer(unsigned int count)_, která vypne časovač, nastaví hodnotu _CMR_ a následně jej zapne a funkci _stopTimer(void)_, která slouží k vypnutí časovače.
### Přerušení časovačem
Jednoduchý příkaz _switch_, který podle stavu programu uskuteční buď dlouhé zmáčknutí nebo dekódování.
### Vstup
Vstup je řešen jako zmáčknutí tlačítka _SW5_ a ukládá se do pole charakterů o velikosti maximálního počtu znaků _MAX_LENGTH_. Případné neustálé držení tlačítka je ošetřené tak, že je potřeba pustit tlačítko pro zadávání dalšího znaku. Samotné neustále držení tlačítka nezastavuje průběh programu. (Pokud byly zadány znaky, dekódují se.)
### Dekódování
Morseovy kódy jsou uloženy v dvourozměrném konstantním poli charakterů. Pro dekódování se volá funkce _decodeMorse(void)_, která prochází skrze pole s kódy a porovnává je se vstupem funkcí _strcmp()_. Při nalezené shodě zavolá funkci _sendChar(char c)_, která uskuteční komunikaci přes UART. Při nenalezení vrací neúspěch a dojde k vypsání chybové hlášky. 

Program má uložené Morseovy kódy latinky a čísel 0-9.
### Reproduktor
Spouští se zavoláním funkce _beep(unsigned int count)_, kde _count_ určuje délku pípnutí. Využívá funkce _delay(unsigned int count)_, která provádí zpoždění použitím ___NOP()_.
### Nastavení UART
* 115200 Bd
* 8 bit
* 1 stop bit
* no parity
* no flow management
## Závěr
Program splňuje zadání projektu s rozšířením o čísla.