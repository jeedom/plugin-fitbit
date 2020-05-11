Plugin für den Zugriff auf Informationen zu Fitbit-Geräten
(Anzahl der Schritte, Kalorien, Entfernung…)

Konfiguration 
=============

Sobald das Fitbit-Plugin installiert ist, gehen Sie zu :

![fitbit 1](../images/fitbit-1.JPG)

Geräteerstellung und Zuordnung zum Fitbit-Konto 
--------------------------------------------------------

> **Spitze**
>
> Setzen Sie die Maus wie an vielen Stellen auf Jeedom ganz links
> ruft ein Schnellzugriffsmenü auf (Sie können
> von deinen Profilen immer sichtbar lassen)

Klicken Sie auf Person hinzufügen, um jemanden hinzuzufügen :

![fitbit 2](../images/fitbit-2.JPG)

Geben Sie dieser Person (diesem Gerät) einen Namen und validieren Sie :

![fitbit 3](../images/fitbit-3.JPG)

Sie sollten dann diese Seite haben :

![fitbit 4](../images/fitbit-4.JPG)

> **Wichtig**
>
> Das erste, was überprüft werden muss, ist "URL zurückgeben"". Das muss sein
> validiert und von außen zugänglich, sonst können Sie nicht
> Verknüpfe Jeedom mit deinem Fitbit-Konto. Wenn nicht, setzen Sie
> Aktualisieren Sie Ihre Netzwerkkonfigurationseinstellungen unter Allgemein →
> Administration → Konfiguration dann Teil "Netzwerke", siehe
> [hier](https://www.jeedom.fr/doc/documentation/core/de_DE/doc-core-administration.html.html#administration)

> **Notiz**
>
> Es wird empfohlen, zwischen Jeedom und Fitbit eine Verbindung herzustellen
> zu Jeedom durch die externe IP

Um Ihr Fitbit-Konto mit Jeedom zu verknüpfen, erstellen Sie es zuerst
ein Fitbit-Entwicklerkonto, indem Sie gehen
[hier](https://dev.fitbit.com/fr), dann müssen Sie sich anmelden
dann tu es : "Registrieren Sie eine App". Dann geben Sie die Informationen ein
:

-   Anwendungsname : Jeedom

-   Beschreibung : Jeedom

-   Application Website : <https://jeedom.com>

-   Organisation : jeedom

-   Organisation Website : <https://jeedom.com>

-   OAuth 2.0 Anwendungstyp : Server

-   Rückruf-URL : Sie müssen die setzen, die Jeedom Ihnen während der gibt
    Seien Sie vorsichtig, wenn Sie dies nicht tun
    Gut, die Verbindung kann nicht hergestellt werden

-   Standardzugriffstyp : Lesen & Schreiben

Sie können auch ein Abonnement hinzufügen, dies ermöglicht Fitbit
Schieben Sie ein Update direkt in Jeedom (anstatt darauf zu warten
Jeedom Update Informationen) :

-   Standard : oui

-   Endpunkt-URL : der von jeedom gegeben

-   Typ : JSON-Körper

-   Teilnehmer-ID : Setzen Sie nichts

Wenn Sie ein Abonnement haben, müssen Sie (vorsichtig sein, alles zu tun
diese Schritte in der Reihenfolge) :

-   Tragen Sie die Abonnementnummer in die Fitbit-Ausrüstung von Jeedom ein

-   Geben Sie den Abonnementcode in die Fitbit-Ausrüstung von Jeedom ein

-   Ausrüstung sichern

-   Starten Sie auf der Fitbit-Website die Überprüfung des Abonnements

-   Dann "Link zu einem Benutzer" der Jeedom-Ausrüstung, wie unten

> **Wichtig**
>
> Derzeit unterstützt Fitbit https schlecht, wenn Sie sich also in https befinden
> Es gibt kaum eine Chance, dass es funktioniert

Klicken Sie auf "Mit Benutzer verknüpfen", um dieses Gerät mit Ihrem zu verknüpfen
Fitbit-Konto :

![fitbit 5](../images/fitbit-5.JPG)

Geben Sie Ihre Anmeldeinformationen für das Fitbit-Konto ein und überprüfen Sie die
Autorisierungsanfrage :

Wenn Sie eine leere Seite oder einen Fehler erhalten, wird Ihr
Die Netzwerkkonfiguration ist nicht gut, andernfalls müssen Sie darauf zurückgreifen
diese Seite :

![fitbit 6](../images/fitbit-6.JPG)

Für das Abonnement benötigen Sie

Konfiguration 
-------------

Hier sind die Details der Plugin-Konfiguration :

![fitbit 6](../images/fitbit-6.JPG)

-   Name der Person : Name der Fitbit-Ausrüstung

-   Übergeordnetes Objekt : Name des Objekts, an das das Gerät angeschlossen werden soll

-   Aktivieren / Sichtbar : aktiviert das Gerät (nicht vergessen
    Tun Sie es, sonst haben Sie keine Daten) und machen Sie es sichtbar
    auf dem Armaturenbrett

-   Für jede Bestellung :

    -   Chronik : ermöglicht das Protokollieren des Befehls

    -   Anzeige : ermöglicht es, es im Dashboard sichtbar zu machen oder nicht

    -   Fortgeschrittene (kleine gekerbte Räder) : zeigt die
        erweiterte Steuerungskonfiguration

    -   Test : Ermöglicht das Testen des Befehls, um seinen Wert anzuzeigen

    -   Löschen (Schaltfläche "-") : um die Bestellung zu löschen

Widget 
------

Hier ist das Fitbit-Widget :

![fitbit 7](../images/fitbit-7.JPG)
