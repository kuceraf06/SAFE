# SAFE – web a administrace

Web slavnostního galavečera **SAFE** baseballového a softballového klubu
Miners Kladno. Kompletní přestavba původního webu do struktury MVC se stejným
uspořádáním a zabezpečením jako projekt SCM.

## Struktura projektu

```
index.php                  # front controller (routování, ?route=)
app/
  views/cs/                # české pohledy (home, about, sponsors, events,
  views/en/                # anglické pohledy   awards, program, reservation, contact)
  views/layout/            # head, header (navigace), footer
  lib/                     # data.php (čtení z DB), view_helpers, sendmail
admin/
  index.php                # rozcestník (dashboard)
  termin/ invitation/      # správa termínu a pozvánky
  program/ reservation/    # správa programu a vstupenek (zap/vyp)
  events/ awards/          # minulé ročníky (Quill editor) a ocenění hráči
  account/ login/ logout/  # účet a přihlášení
  assets/admin.css         # styly administrace (barvy SAFE)
  lib/                     # bootstrap, auth, db, helpers, seed, photo_field
config/
  config.php               # konfigurace (JE v gitu - varianta A)
  config.example.php       # šablona konfigurace
  secrets.example.php      # šablona pro SendGrid klíč (secrets.php NENÍ v gitu)
storage/db/                # SQLite databáze (NENÍ v gitu, vytvoří se sama)
public/
  css/ js/ images/         # statické soubory
```

## Nasazení na server (jak to funguje)

Projekt je nastavený tak, aby stačilo **stáhnout z gitu a nahrát na server**.

1. **Kolega stáhne repozitář z gitu a nahraje soubory na server.** Hotovo.

2. **Databáze se vytvoří sama** při prvním otevření stránky, která ji
   potřebuje (Minulé ročníky, Ocenění, Program, nebo přihlášení do
   administrace). SQLite databáze je jen soubor – PHP ho vytvoří v
   `storage/db/safe.sqlite` a naplní stávajícím obsahem (92 ocenění,
   4 ročníky, program, termín). Není potřeba nic spouštět z terminálu.

3. **Přihlašovací údaje jsou v `config/config.php`**, který je součástí gitu
   (varianta A). Kolega tedy nic nenastavuje. Heslo lze kdykoliv změnit přímo
   v administraci (sekce *Změna hesla*).

### E-maily (kontaktní formulář)

Odesílání e-mailů z kontaktu používá SendGrid. Klíč patří do `config/secrets.php`
(kopií z `config/secrets.example.php`), který **není v gitu**. Bez klíče web
funguje, jen se z formuláře neodešle e-mail. Klíč na server doplní správce
jednorázově.

### Aktualizace projektu (bez ztráty dat)

Když do gitu pošleš novou verzi kódu a kolega ji přetáhne:
- **Databáze se NEPŘEPÍŠE**, protože v gitu není (je v `.gitignore`). Přetažení
  z gitu se dotkne jen kódu, ne živé databáze na serveru.
- Živá data (změny přes administraci) tak zůstanou zachovaná.

## Administrace

Přihlášení: `/admin/login/`

- **Termín** – datum a čas akce (zobrazuje se na úvodu a v sekci O akci)
- **Pozvánka** – obrázek pozvánky (sekce Vstupenky)
- **Program** – řádky programu galavečera (CZ + EN)
- **Vstupenky** – přepínač, zda jsou rezervace zapnuté nebo vypnuté
- **Minulé ročníky** – správa ročníků s textovým editorem **Quill** (zdarma,
  bez registrace); nadpis a popis lze formátovat (tučně, kurzíva, nadpisy,
  seznamy, odkazy), ke každému ročníku patří galerie obrázků
- **Ocenění hráči** – správa ocenění: rok, kategorie, nadpis, popis, fotka
  (CZ + EN); v seznamu lze filtrovat podle roku
- **Změna hesla** – změna přihlašovacího hesla

## Zabezpečení

- Heslo jen jako hash (bcrypt), nikde v kódu natvrdo
- Ochrana proti CSRF u všech formulářů
- Prepared statements (žádná SQL injection)
- Bezpečný upload obrázků (ověření skutečného obsahu přes `getimagesize`,
  náhodné názvy souborů)
- Sanitizace HTML z editoru (odstranění `<script>`, `on*` atributů apod.)
- Databáze mimo web root, chráněná `.htaccess`

## Lokální vývoj

```
php -S 127.0.0.1:8000
```

Web běží na `http://127.0.0.1:8000/`, administrace na
`http://127.0.0.1:8000/admin/login/`.
