# Lynt Leads WordPress plugin (prototype)

Toto je miniaturní WordPress plugin, který zachytává poptávky z kontatkního formuláře a umožňuje s nimi dále pracovat.

Pro svou práci se snaží maximálně využívat funkce jádra WordPress.

Po odeslání poptávky dojde k jejímu uložení jako nový typ obsahu Lead a lze posouvat do dalších stavů. Každá změna stavu je poslána do Google Analytics jako údálost. U finálního stavu "Úspěšný Lead" lze nastavit také hodnotu zisku.

Výchozí stavy:

- Nový Lead / new
- Dobrý Lead / good
- Špatný Lead /bad (finální stav)
- Odložený Lead / wait
- Neúspěšný Lead / lost (finální stav)
- Úspěšný Lead / win (finální stav s nastavením hodnoty)

Aktuálně podporuje kontaktní formulář pluginu [Contact Form 7](https://cs.wordpress.org/plugins/contact-form-7/).

## Nastavení
V Nastavení > Lynt Leads je nutné nastavit ID Google Analytics účtu (UA-xxxxxxxx-xx).

## Události Google Analytics
Při každé změně stavu se posílá událost v následujícím formátu:
- Category: Lynt Lead
- Action: status (new, bad, good, wait, lost, win)
- Label: ID poptávky
- Value: hodnota nastavená ve stavu "Úspěšný Lead"


## Integrace s Contact Form 7
Aby byl kontaktní formuář zachytáván, je třeba do formuláře doplnit skryté formulářové pole lynt_tag s názvem kontaktního formuláře (např. Poptávka, Podpora atd.). Lze tak odlišit několik různých formulářu na webu.

```[hidden lynt_tag "Poptávka"]```

Dále lze použít další 2 nepovinná pole pro párování e-mailové adresy a jména odesílatele s konkrétním polem formuláře pro větší přehlednost seznamu poptávek.

Pokud se vaše formulářové políčko s e-mailem jmenuje "your-email" a formulářové políčko se jménem "your-name", bude mapování vypadat následovně:

```
[hidden lynt_name "your-name"]
[hidden lynt_mail "your-email"]
```

## Pro vývojáře
Stavy poptávky jsou definovány PHP polem:

```
$statuses =  array(
    "lynt_new"  => array("name"=>"Nový",      "next"=>array('lynt_good','lynt_bad')),
    "lynt_good" => array("name"=>"Dobrý",     "next"=>array('lynt_win', 'lynt_wait','lynt_lost')),
    "lynt_bad"  => array("name"=>"Špatný",    "next"=>array('lynt_new')),
    "lynt_wait" => array("name"=>"Čekající",  "next"=>array('lynt_win','lynt_bad')),
    "lynt_win"  => array("name"=>"Úspěšný",   "next"=>array()),
    "lynt_lost" => array("name"=>"Neúspěšný", "next"=>array()),
   );
```

## TODO
- definice vlastních stavů pomocí hooku
- dodělání možnosti překladu
 
