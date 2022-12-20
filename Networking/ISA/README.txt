Popis programu

Tento program byl napsán jako projekt do předmětu Síťové aplikace a správa sítí. 
Cílem projektu bylo implementovat v C/C++ stranu serveru a klienta pro http nástěnku. 
Strana serveru má za úkol spravovat API a odpovídat na požadavky od klienta. 
Strana klienta má za úkol vytvářet požadavky pro server na základě předaných argumentů a 
tisknout odpovědi serveru.

Spuštění a příklad

Server 
./isaserver -p <port> [-h]

./isaserver -p 2020

Klient
./isaclient -H <host> -p <port> <command> [-h]

./isaclient -H 127.0.0.1 -p 2020 boards
./isaclient -H localhost -p 2020 item add name content
./isaclient -H merlin.fit.vutbr.cz -p 2020 item add name "content content content"

content je omezen na znaky [a-zA-Z0-9]

Seznam souborů

isaserver.cpp - logika serveru
isaserver.h - knihovna k serveru
isaclient.cpp - logika klienta
isaclient.h - knihovna ke klientovi
api.cpp - logika API
api.h - knihovna API

Makefile

make all - přeloží vše
make rm - smaže binárky a objekty

Rozšíření

Server zachytává escape sekvenci "ctrl + c". 
Následně smaže všechny objekty a ukončí se.

Při chybových stavech server podá informace v těle odpovědi.

Omezení

Buffery jsou nastavené na 4kiB.
