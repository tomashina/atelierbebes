# Lokalni prijenos Libermedia sadržaja — 21. 10. 2026.

Izvor: lokalni OpenCart 3.0.3.8, `/Users/tomek/Herd/libermedia`, MySQL baza `libermedia`.
Odredište: `/Users/tomek/Herd/libermedia-new`, vlastita SQLite baza.

## Preneseno i uspoređeno s izvorom

| Sadržaj | Broj |
|---|---:|
| Proizvodi | 2725 |
| Kategorije proizvoda | 51 |
| Proizvođači | 40 |
| Blog zapisi | 585 |
| Informacijske stranice | 5 |
| Kupci | 574 |
| Adrese | 714 |
| Narudžbe | 6400 |
| Stavke narudžbi | 11159 |
| Zbrojevi narudžbi | 20452 |
| Povijest narudžbi | 10693 |
| Slike u Laravel media biblioteci | 8250 |
| Jedinstvene valjane newsletter adrese | 1368 |

Provjereni su izvorni ID-jevi i postojanje ciljnih zapisa za sve navedene glavne entitete. Nema nedostajućih mapiranja. Usporedba svih osnovnih cijena, zaliha i aktivnih statusa proizvoda te ukupnih iznosa narudžbi nema odstupanja. Od 2725 proizvoda izvor ima 880 aktivnih i 1845 neaktivnih; statusi su očuvani.

Početna stranica, katalog, blog, uzorak proizvoda, kategorije, informacijske stranice, blog članka i medijske datoteke vraćaju HTTP 200. Ponovni uvoz medija i newslettera dodao je 0 zapisa, bez duplikata.

## Iznimke i preostala prilagodba

- 70 referenci na slike nema odgovarajuću datoteku u lokalnom OpenCart izvoru: 69 blog referenci i jedna proizvodna referenca s putanjom `catalog/Soho/undefined`.
- 20 blog zapisa nema dostupni prijevod u izvoru te nisu javno aktivirani. Dva prijevoda koriste jezik koji izvor nema definiran; sačuvani su u privatnoj arhivi.
- 64 duplicirana SKU-a dobila su nastavak izvornog ID-ja kako se proizvodi ne bi spojili. Originalni podaci ostaju u payloadu.
- Postoji 1485 valjanih SEO preusmjeravanja, 2748 nerazriješenih povijesnih putanja i 16 sukoba putanja. Neaktivni ili uklonjeni ciljevi nisu automatski preusmjereni na drugi sadržaj.
- 433 kupca nemaju valjani OIB, a 441 zapis zahtijeva provjeru adrese za račun. To su provjere naslijeđenog B2B importera; za Libermedia B2C tok treba prilagoditi pravila kupaca, registracije i checkouta prije puštanja u rad.
- Cjenik s 3200 osnovnih i akcijskih pravila prenesen je kao nacrt. Akcijska pravila zahtijevaju zasebnu provjeru i aktivaciju; osnovne cijene su prenesene u proizvode.
- Originalne slike su prenesene. Generiranje novih veličina i WebP varijanti ostavljeno je za potrebe novog dizajna.
- Newsletter izvor ima 1369 redaka; preneseno je 1368 jedinstvenih valjanih adresa. Nije izmišljena evidencija privole niti uključen vanjski newsletter servis.
- OpenCart moduli, posebna SEO polja i ostale nestandardne tablice nisu automatski pretvoreni u Laravel funkcionalnosti. Potpuna izvorna baza sačuvana je privatno za daljnju prilagodbu; standardni importer dodatno arhivira 64926 izvornih zapisa.

## Privatni lokalni trag

U `storage/app/private/` nalaze se:

- `libermedia-source-2026-10-21.sql.gz`: potpuna sigurnosna kopija izvora;
- `libermedia-assets-report.json`: popis nedostajućih medija i rezultati zadnjeg prolaza;
- `libermedia-reconciliation.json`: agregatna usporedba izvora i odredišta.

Ove datoteke, baza, originalne slike i `.env` nisu verzionirani. Izvorna OpenCart trgovina nije mijenjana. Odlazna pošta ide u lokalni log; integracije nisu povezane s produkcijom.
