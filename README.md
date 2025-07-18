# API Backend Raspadinha

## Configuração

1. **Banco de Dados**
   - Importe o arquivo `database.sql` no seu MySQL
   - Configure as credenciais em `api/config/database.php`

2. **Servidor Web**
   - Configure seu servidor para apontar para a pasta `api/`
   - Certifique-se que o mod_rewrite está habilitado

3. **Usuário Admin Padrão**
   - Email: admin@raspadinha.com
   - Senha: admin123

## Endpoints da API

### Autenticação
- `POST /api/auth/register` - Registrar usuário
- `POST /api/auth/login` - Login

### Usuário
- `PATCH /api/user` - Atualizar dados do usuário

### Pagamentos
- `POST /api/payments/deposit` - Criar depósito
- `POST /api/payments/withdraw` - Solicitar saque

### Transações
- `GET /api/transactions` - Listar transações do usuário

### Admin (requer is_admin = true)
- `GET /api/admin/dashboard` - Estatísticas gerais
- `GET /api/admin/users` - Listar todos usuários
- `GET /api/admin/transactions` - Listar todas transações

### Webhooks
- `POST /api/webhooks/nitro` - Webhook da Nitro Pagamentos

## Estrutura de Resposta

Todas as respostas seguem o padrão JSON com status HTTP apropriado.

### Exemplo de Login Bem-sucedido:
```json
{
  "message": "Login realizado com sucesso.",
  "user": {
    "id": 1,
    "username": "usuario",
    "email": "usuario@email.com",
    "phone": "11999999999",
    "document": "12345678901",
    "balance": 100.50,
    "is_admin": false,
    "stat": {
      "deposit_sum": 200.00,
      "withdraw_sum": 99.50
    }
  },
  "token": "base64_encoded_token"
}
```

## Segurança

- Senhas são criptografadas com bcrypt
- Tokens de autenticação são validados em endpoints protegidos
- CORS configurado para permitir requisições do frontend
- Validação de dados de entrada em todos os endpoints