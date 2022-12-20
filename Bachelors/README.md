# Lokalizace šachových figurek na hrací desce z fotografie
**autor :** Tomáš Hampl (xhampl10)
**email :** xhampl10@stud.fit.vutbr.cz
**datum :** 11.05.2021

# Úvod
Cílem této práce bylo analyzovat stav šachovnice a lokalizovat šachové figurky na šachovnici z fotografie. Problém byl rozdělen do tří částí - detekce šachovnice, detekce figurek a lokalizace. Detekce šachovnice je založená na lokalizace hran v obraze. K detekci čar se využívá Houghovy transformace a algoritmu PClines. K detekci šachových figurek se využívá konvoluční neuronová síť. Konkrétně se využívá modelů YOLOv3 a YOLOv4. Lokalizace je uskutečněná spojením výstupů předchozích částí. V zájmu představení výsledků byla implementována demo aplikace.

# Závislosti
Použité knihovny k implementaci.
- OpenCV  4.5.1.48 
- Numpy 1.19.5
- Chess 1.4.0
- PyQT5 5.15.4
- matplotlib 3.3.4
- pclines 1.0.2
- statistics 1.0.3.5

# Soubory
- boardAnnotation.py - pomocný skript k anotaci rohů šachovnice
- boundingBoxes.py - skript na vytváření ohraničujících rámečků
- chessboardDetectionHoughlines.py - skript pro detekci šachovnice pomocí Houghovy transformace
- chessboardDetectionPClines.py - skript pro detekci šachovnice pomocí PClines
- demo.py - PyQT5 demo aplikace
- evaluateChessboardDetection.py - skript na vyhodnocení detekce šachovnice
- figureAnnotation.py - pomocný skript k anotaci figurek
- figureDetecion.py - skript pro detekci šachových figurek
- midPoinAnnotation.py - pomocný skript k anotaci středu šachovnice
- plotAvgLoss.py - pomocný skript k vykreslení grafu logu tréninku neuronové sítě

# Spouštění souborů
### boardAnnotation.py
Pomocný skript k vytváření anotací rohů šachovnice.

Soubor obsahuje třídu **BoardAnnotation**. Program se volá funkcí **create_annotation**, která má povinný parametr **directory** - cesta k složce s obrázky, které chce anotovat. 

> python3 boardAnnotation.py directory

### boundingBoxes.py
Skript vytváří ohraničující rámečky pro figurky z předem známých pozic a anotací rohů šachovnice. Využívá homografie, kalibrace kamery a odhadu pozice.

Soubor obsahuje třídu **BoundingBoxes**, která zapouzdřuje běh programu. Program se spouští funckí **create_boudning_boxes** má jeden povinný argument - **directory**. Nepovinný argument k určení, zda uživatel chce vykreslit kvádry políček - **draw_boxes** a druhý nepovinný argument pro nastavení velikosti figurek - **sizes**.

> python3 boundingBoxes.py directory

### chessboardDetectionHoughlines.py
Skript implementuje detekci šachovnici pomocí funkce **HoughlinesP*.

Soubor obsahuje třídu **ChessBoardDetection**. Při inicializaci je možné měnit parametry pro detekci čar. Běh programu se spouští funkcí **detect_chessboard**, která má povinný argument **image** - cesta k obrázku. Program vrací mřížku středů políček šachovnice.

> python3 chessboardDetectionHoughlines.py image

### chessboardDetectionPClines.py
Skript implementuje detekci šachovnici pomocí algoritmu PClines.

Soubor obsahuje třídu **ChessBoardDetection**. Při inicializaci je možné měnit parametry pro detekci čar. Běh programu se spouští funkcí **detect_chessboard**, která má povinný argument **image** - cesta k obrázku. Program vrací mřížku středů políček šachovnice.

> python3 chessboardDetectionPClines.py image

### demo.py
Implementace jednoduché demoaplikace pomocí PyQT5. Při inicializaci se můžou měnit parametry cest ke konfiguračním souborům konvoluční neuronové síti. Defaultně se používá YOLOv4.

> python3 demo.py

### evaluateChessBoardDetection.py 
Skript pro testování detekce šachovnice.

> python3 evaluateChessBoardDetection.py directory
* Je lepší tento program prvně otevřít v IDE a nastavit podle požadavků.

### evaluateLocalization.py
Skript pro testování lokalizace stavu šachovnice.

> python3 evaluateLocalization.py directory
* Je lepší tento program prvně otevřít v IDE a nastavit podle požadavků.

### figureAnnotation.py
Pomocný skript pro anotaci šachových figurek.

Soubor obsahuje třídu **FigureAnnotation**. Program se spouští funkcí **create_annotation** s poviným argumentem **directory**, který určuje cestu k adresáři s obrázky.

> python3 figureAnnotation.py directory

### figureDetecion.py
Skript pro detekci a lokalizaci šachových figurek na šachovnici.

Soubor obsahuje třídu **FigureDetection**, která zapouzdřuje běh programu. Při inicializaci je možné nastavit konfigurační soubory neuronové sítě. Program se spouští funkcí **detect_figures**, která má dva povinné argumenty **image** - cestu ke snímku a **chessboard_center_points** - výstup detekce šachovnice. Program vrací predikci stavu v podobě notace FEN.

> python3 figureDetecion.py image

### midPointAnnotation.py
Pomocný skript k anotaci středů šachovnic.

Soubor obsahuje třídu **MiddlePointAnnotation**. Běh programu se spouští funckí **create_annotation** s povinným argumentem **directory** - cesta ke složce se snímky.

> python3 midPointAnnotation.py directory

### plotAbgLoss.py
Pomocný skript k vytvoření grafů z logovacích souborů vzniklých při trénování neuronové sítě.