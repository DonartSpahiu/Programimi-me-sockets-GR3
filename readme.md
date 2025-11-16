# Projekti 2 - Programimi me Sockets

- Ky projekt demonstron komunikimin klient-server përmes protokollit UDP, duke mundësuar lidhjen e disa klientëve (të paktën 4) me një server në një rrjet real. Serveri menaxhon kërkesat, ruan logimet, dhe ofron privilegje të ndryshme për përdoruesit standard dhe ata me të drejta admin.

## Përshkrimi

- Serveri dëgjon në një port dhe IP të caktuar, pranon lidhje nga klientët dhe menaxhon kërkesat e tyre.
- Regjistron numrin e lidhjeve, IP-të e klientëve aktivë, numrin e mesazheve dhe trafikun total në një file server_stats.txt.
- Nëse një klient shkëputet, serveri e mbyll dhe mund ta rikuperojë automatikisht.
- Një klient me privilegje të plota mund të ketë qasje në file-t e serverit.

## Funksionalitetet e klientit

- Lidhje me serverin përmes UDP socket.
- Klienti admin mund të përdorë komandat:

/list
/read <filename>
/upload <filename>
/download <filename>
/delete <filename>
/search <keyword>
/info <filename>

- Klientët e tjerë kanë vetëm lexim (read-only).
- Mesazhet dhe përgjigjet shfaqen në terminal.

## Struktura

-server.php        # Serveri UDP
-client.php        # Klienti UDP
-server_files/     # Folder me file-t në server
-server_stats.txt  # Log i statistikave të trafikut
-server_log.txt    # Regjistrimi i aktiviteteve

## Ekzekutimi

Startimi i serverit
- Hapni terminalin dhe shkoni te folderi i projektit: cd path/to/Project
- Startoni serverin: php server.php
- Serveri do të presë lidhje nga klientët dhe regjistron aktivitetet në server_log.txt dhe statistikat në server_stats.txt.

Startimi i klientit
- Hapni terminal të ri për çdo klient dhe shkoni te folderi i projektit: cd path/to/Project
- Startoni klientin: php client.php
- Klienti lidhet me serverin dhe mund të përdorë komandat e autorizuara.

## Anëtarët e grupit

1. Donart Ajvazi
2. Donart Spahiu
3. Dren Xhyliqi
4. Edison Ndou

