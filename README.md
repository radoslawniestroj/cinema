# Kino

Aplikacja obsługuje podstawowy mechanizm do obsługi kina za pośrednictwem API.

## Instalacja aplikacji

Instalacja projektu:
- uruchom komendę `docker-compose up -d`, pobiera i konfiguruje kontenery potrzebne do działania
- następnie `docker-compose exec php bash`, za jej pomocą dostajesz się do kontenera
- w kontenerze `composer install`, instaluje brakujący kod
- następnie `symfony console lexik:jwt:generate-keypair`, utworzy klucze wymagane do poprawnego działania autoryzacji JWT
- uruchom komendę `symfony console doctrine:migrations:migrate -n`, uruchomi migrację, która utworzy tabele w bazie danych
- (opcjonalnie) `symfony console doctrine:fixtures:load --append`, wypełni tabele przykładowymi danymi

I to już wszystko! Masz działający projekt kina. Sprawdź czy działa i odwiedź stronę [localhost](http://localhost:8080/).</br>
Polecam się teraz zapoznać z wypisanymi w spisie treści dostępnymi funkcjonalnościami.</br>
\* pamiętaj o tym, że niektóre przeglądarki jak np. Google Chrome blokują domeny http, w takim wypadku musisz zezwolić na działanie localhost w ustawieniach

## Api
Każde zapytanie, z wyjątkiem logowania, jest zabezpieczone przed dostępem dla niezalogowanych użytkowników.
Pamiętaj, aby przed jego wywołaniem ustawić otrzymany token w nagłówku autoryzacji (barierę). Wszystkie opisane zapytania zawierają już przykładowe dane.

### Logowanie
\* do obsługi logowania została użyta autoryzacja JWT (lexik/jwt-authentication-bundle)</br>
POST `{{domain}}/api/auth/login`
```json
{
    "email": "admin@email.com",
    "password": "Password123"
}
```
</br>odpowiedź
```json
{
    "token": "eyJ0eXAiO...."
}
```

### Pobranie sal z wolnymi miejscami
GET `{{domain}}/api/halls`
</br>odpowiedź
```json
[
    {
        "id": 1,
        "name": "hall1",
        "seats": [
            {
                "id": 1,
                "rowNumber": 1,
                "seatNumber": 1
            },
            {
                "id": 2,
                "rowNumber": 1,
                "seatNumber": 2
            },
            ...
        ]
    }
]
```

### Dodanie rezerwacji
\* rezerwacja zostanie przypisana do aktualnego zalogowanego konta</br>
POST `{{domain}}/api/reservation-create`
```json
{
    "seatId": 1
}
```
</br>odpowiedź
```json
{
    "id": 2
}
```

### Usunięcie rezerwacji
DELETE `{{domain}}/api/books/{id}`
```json
{
    "reservationId": 2
}
```
</br>odpowiedź
```json
{
    "message": "Cancelled"
}
```

### Pobranie wszystkich informacji na temat sali
\* tą czynność może wykonać użytkownik z rolą `ADMIN`</br>
GET `{{domain}}/api/admin/hall`
</br>odpowiedź
```json
[
    {
        "id": 1,
        "name": "hall1",
        "seats": [
            {
                "id": 1,
                "rowNo": 1,
                "seatNumber": 1,
                "reservations": []
            },
            {
                "id": 2,
                "rowNo": 1,
                "seatNumber": 2,
                "reservations": []
            },
            ...
        ]
    }
]
```

## Utworzenie sali
POST `{{domain}}/api/admin/hall`
\* tą czynność może wykonać użytkownik z rolą `ADMIN`</br>
```json
{
    "name": "Hall 1",
    "rowsNumber": 5,
    "seatsNumber": 5
}
```
</br>odpowiedź
```json
{
    "id": 1
}
```

## Modyfikacja sali
PUT `{{domain}}/api/admin/hall/{id}`
\* tą czynność może wykonać użytkownik z rolą `ADMIN`</br>
```json
{
    "name": "Hall 2",
    "rowsNumber": 3,
    "seatsNumber": 3
}
```
</br>odpowiedź
```json
{
    "id": 2,
    "name": "hall2",
    "seats": [
        {
            "id": 101,
            "rowNo": 1,
            "seatNumber": 1,
            "reservations": []
        },
        {
            "id": 102,
            "rowNo": 1,
            "seatNumber": 2,
            "reservations": []
        },
        ...
    ]
}
```

## Usunięcie sali
DELETE `{{domain}}/api/admin/hall/{id}`
\* tą czynność może wykonać użytkownik z rolą `ADMIN`</br>
</br>odpowiedź
```json
{
    "success": true
}
```
