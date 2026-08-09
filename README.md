# Trustindex interjú teszt feladat

**Készítő:** Hofferek Attila (<azhofi@gmail.com>)

---

## Rövid leírás

A projekt egy Symfony alapú értékeléskezelő webalkalmazás, amely beérkezett cégértékelések kezelésére és elemzésére szolgál.

### Főbb funkciók:
* **Értékelések kezelése (CRUD):** Új értékelés beküldése, meglévő értékelések részletes megtekintése és szerkesztése.
* **Összesített cégstatisztika:** Cégek szerinti aggregált kimutatás az értékelések számáról és az átlagos értékelési pontszámról (csökkenő sorrendben).
* **Modern UI:** Bootstrap 5 és Symfony UX Icons (Tabler ikonkészlet) integráció.
* **Automata tesztek:** Teljes körű funkcionális (Controller) és integrációs (Repository) tesztek PHPUnit-tal, automatikus teszt-adatbázis inicializálással.

---

## Előfeltételek

* **PHP:** 8.2 vagy újabb
* **Composer:** 2.x
* **Symfony CLI:** (Ajánlott a fejlesztői szerverhez és parancsokhoz)

---

## Telepítés és beállítások

### 1. Projekt klónozása és függőségek telepítése

```
git clone git@github.com:AzHofi76/trustindex-task.git
cd trustindex-task
```

### 2. PHP függőségek telepítése
```
composer install
```

### 3. tesztek futtatása
```
php bin/phpunit
```

### 4. symfony szerver indítás
```
symfony serve
```

---

## Időráfordítás

A favágó feladatokhoz a gemini AI-t vettem igénybe
A kiindulási alap a Symfony demo projekt volt - utólag nem biztos, hogy újra ezzel kezdeném, elég sok időbe telt a felesleges dolgok kidobása

* entity létrehozása 10 perc
* form type létrehozása 15 perc
* repository létrehozása 30 perc
* kontroller létrehozása 
* nézetek létrehozása 60-70 perc
* tesztek létrehozása 60-70 perc