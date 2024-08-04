Para rodar localmente:

cd ./public

php -S localhost:3333



DOCUMENTAÇÃO ENDPOINTS:

https://documenter.getpostman.com/view/25407001/2sA3rwNZwt





API LiquidPay Backend
Funcionalidades da API



POST 

Host: localhost:3333/

Route: http://localhost:3333/register 

Content-Type: application/json


{

    "name": "Giovana",
    
    "cpf": "00949196073",
    
    "email": "giovana@gmail.com",
    
    "password": "password"
}

Response:
{

    "message": "User registered successfully"
    
}



//////////////////////////////////



POST 

Host: localhost:3333/

Route: http://localhost:3333/login 

Content-Type: application/json

{

  "cpf": "00949196071", 
  "password": "password2" 
  
}


or


{

  "email": "email@dominio.com",  
  "password": "password2"
  
}

response: 
{

    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJ5b3VyLWRvbWFpbi5jb20iLCJzdWIiOjEsImlhdCI6MTcyMjcxNzA3MywiZXhwIjoxNzIyNzIwNjczfQ.h9vQNeqoH9AKQdwptmmT4g-cBUGvf8xRpDffsbygI84"
    
}



//////////////////////////////////



POST 

Host: localhost:3333/

Route: http://localhost:3333/change-password 

Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

Content-Type: application/json

{

    "user_id": 1,
    "current_password": "password3",
    "new_password": "password4"
    
}

response: 

"{\"authMessage\":\"Authentication successful\",\"routeMessage\":\"You have accessed a protected route\",\"authenticated\":true}"{

    "message": "Senha alterada com sucesso"
    
}



//////////////////////////////////



GET 

Host: localhost:3333/

Route: http://localhost:3333/users

Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

Content-Type: application/json

response: 

"{\"authMessage\":\"Authentication successful\",\"routeMessage\":\"You have accessed a protected route\",\"authenticated\":true}"[
    {
    
        "id": 1,
        "name": "Leonardo",
        "cpf": "00949196071",
        "email": "leonardolino@gmail.com",
        "password": "$2y$10$nqGX1HaaSqSeizmYgC2ThODb8o4ch6bJnv3Kld1Lh6/2VoZEnuiVW",
        "balance": "100.00"
        
    },
    {
    
        "id": 2,
        "name": "Martins",
        "cpf": "00949196072",
        "email": "martinso@gmail.com",
        "password": "$2y$10$zljBBSSk0gEXJrotgV7Vwu4f91.a6wClZph9/1n0Ut15reW4NW7ZO",
        "balance": "2100.00"
        
    },
    {
    
        "id": 3,
        "name": "Giovana",
        "cpf": "00949196073",
        "email": "giovana@gmail.com",
        "password": "$2y$10$8KkOPQBGW9jl1GQtZjBS8ulwLnr2J7/fq4JzH//ycl7ZSMfgj/PGa",
        "balance": "0.00"
        
    }
]



//////////////////////////////////



POST 

Host: localhost:3333/

Route: http://localhost:3333/api/add-credits

Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

Content-Type: application/json

{

    "card_type": "credit",
    "card_number": "5460750905074011",
    "card_brand": "visa",
    "card_valid": "09/32",
    "card_cvv": "123",
    "amount": 100,
    "user_id": 1
    
}

response: 

"{\"authMessage\":\"Authentication successful\",\"routeMessage\":\"You have accessed a protected route\",\"authenticated\":true}"{

    "message": "registro cadastrado com sucesso em transações"
    
}
{

    "message4": "Créditos adicionados com sucesso no user id: :id"
    
}



//////////////////////////////////



GET 

Host: localhost:3333/

Route: http://localhost:3333/statement?start_date=2023-01-01 00:00:00&end_date=2024-12-31 00:00:00&user_id=1

Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

Content-Type: application/json

response: 

"{\"authMessage\":\"Authentication successful\",\"routeMessage\":\"You have accessed a protected route\",\"authenticated\":true}"[
    
    {
        "id": 1,
        "user_id": 1,
        "amount": "100.00",
        "status": "approved",
        "transaction_id": "6cdecd6e-7451-4a90-aaee-38fe9ff5ba33",
        "created_at": "2024-08-01 21:23:34",
        "typeCard": "debit"
    },
    {
        "id": 2,
        "user_id": 1,
        "amount": "100.00",
        "status": "1",
        "transaction_id": "4671056a-d588-4966-9347-39aa053e7201",
        "created_at": "2024-08-02 19:56:31",
        "typeCard": "credit"
    },
    {
        "id": 3,
        "user_id": 1,
        "amount": "100.00",
        "status": "1",
        "transaction_id": "00efb530-8e55-4ef2-8c31-d3ee9b79a2a0",
        "created_at": "2024-08-02 20:01:03",
        "typeCard": "credit"
    }
    
]



//////////////////////////////////



POST 

Host: localhost:3333/

Route: http://localhost:3333/transfer-credits

Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...

Content-Type: application/json

{

    "user_id": 1,
    "cpf_recipient": "00949196072",
    "amount": 100
    
}

response: 

"{\"authMessage\":\"Authentication successful\",\"routeMessage\":\"You have accessed a protected route\",\"authenticated\":true}"{

    "message": "Atualizado saldo do remetente: :remetente"
    
}{

    "message": "Atualizado saldo do receptor: :receptor"
    
}{

    "message": "Registro da tranferência: :transfer"
    
}{

    "message": "Transferência realizada com sucesso"
    
}
