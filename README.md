# 🌽 Raízes do Nordeste — API REST
### Centro Universitário Internacional UNINTER — Projeto Multidisciplinar (Trilha Back-End / 2026)

API REST completa desenvolvida para a gestão e modernização operacional da rede de lanchonetes nordestinas **"Raízes do Nordeste"**. O sistema atende matriz e filiais com suporte nativo à **multicanalidade** (App, Totem, Balcão, Pick-up e Web), integração desacoplada de pagamento simulado (**Mock Gateway**), controle de estoque regional, conformidade com a **LGPD** e rastreabilidade por **trilha de auditoria**.

---

## 🚀 1. Requisitos de Sistema

* **Linguagem:** PHP 8.2 ou superior (com extensões `php-sqlite3`, `php-mbstring`, `php-intl`, `php-curl`, `php-json`).
* **Gerenciador de Dependências:** Composer 2.x.
* **Banco de Dados:** SQLite3 (banco de arquivos local em `writable/raizes.sqlite`) ou MySQL 8.x.
* **Framework:** CodeIgniter 4.7+.
* **Documentação:** OpenAPI 3.0 / Swagger UI.
* **Testes:** Postman / Insomnia.

---

## ⚙️ 2. Instalação e Execução Passo a Passo

### Passo 1: Clonar o Repositório
```bash
git clone https://github.com/kauanpscode/raizes-api.git
cd raizes-api
```

### Passo 2: Instalar Dependências
```bash
composer install
```

### Passo 3: Configurar Variáveis de Ambiente
Copie o arquivo de exemplo `.env.example` para `.env`:
```bash
cp .env.example .env
```
> O arquivo `.env` já vem pré-configurado por padrão para desenvolvimento local com SQLite em `writable/raizes.sqlite`.

### Passo 4: Executar as Migrações do Banco de Dados
Crie as 10 tabelas relacionais do sistema:
```bash
php spark migrate
```

### Passo 5: Popular Dados Iniciais (Seeder)
Insira as unidades (Matriz Recife e filiais), perfis de acesso, usuários com senha criptografada via `bcrypt`, produtos típicos nordestinos e estoque:
```bash
php spark db:seed DatabaseSeeder
```

### Passo 6: Iniciar o Servidor Local
```bash
php spark serve --port 8080
```
A API estará acessível em: `http://localhost:8080/api`

---

## 📚 3. Documentação Interativa da API (Swagger / OpenAPI)

A documentação interativa Swagger UI pode ser acessada diretamente pelo navegador:
* **URL do Swagger UI:** [http://localhost:8080/swagger/index.html](http://localhost:8080/swagger/index.html)
* **Especificação OpenAPI JSON:** [http://localhost:8080/swagger.json](http://localhost:8080/swagger.json)

Para regenerar a documentação após alterações nos Controllers:
```bash
composer swagger
```

---

## 🧪 4. Como Executar os Testes (Coleção Postman)

O projeto inclui uma coleção completa com **18 cenários de teste automatizados** (cobrindo fluxos positivos e negativos de regras de negócio, validações, estoque e autorização).

* **Arquivo da Coleção:** [`docs/raizes_nordeste_postman_collection.json`](docs/raizes_nordeste_postman_collection.json)

### Ordem de Execução Recomendada:
1. **Autenticação:** Execute a requisição `T01 - Login Válido` (o script do Postman salva automaticamente o Bearer Token na variável `{{token}}`).
2. **Unidades & Cardápio:** Execute `T05 - Listar Unidades` e `T07 - Listar Cardápio por Unidade`.
3. **Criação de Pedido:** Execute `T09 - Criar Pedido com Canal TOTEM` (o script salva o `{{pedidoId}}` retornado).
4. **Validações e Erros:** Teste `T10 - Falta de canalPedido (Erro 422)` e `T11 - Estoque Insuficiente (Erro 409)`.
5. **Pagamento Mock:** Execute `T14 - Pagamento Recusado (Erro 409)` e em seguida `T15 - Pagamento Aprovado (Status 200 -> Pedido PAGO)`.
6. **LGPD & Auditoria:** Execute `T17 - Registrar Consentimento LGPD` e `T18 - Consultar Trilha de Auditoria`.

---

## 🔐 5. Perfis de Usuários Pré-Cadastrados (Seed)

| Usuário | E-mail | Senha | Perfil (Role) | Unidade |
| :--- | :--- | :---: | :---: | :---: |
| **Dona Francisca** | `admin@raizesdonordeste.com.br` | `Admin@123` | `ADMIN` (5) | Matriz Recife |
| **Marcos Oliveira** | `gerente.recife@raizesdonordeste.com.br` | `Gerente@123` | `GERENTE` (4) | Matriz Recife |
| **Juliana Costa** | `atendente.recife@raizesdonordeste.com.br` | `Atendente@123` | `ATENDENTE` (2) | Matriz Recife |
| **Mestre Tião** | `cozinha.recife@raizesdonordeste.com.br` | `Cozinha@123` | `COZINHA` (3) | Matriz Recife |
| **Maria Silva** | `cliente@exemplo.com` | `Senha@123` | `CLIENTE` (1) | — (Consumidora) |

---

## 🏛️ 6. Arquitetura em Camadas (Separation of Concerns)

O sistema segue rigorosamente o princípio de separação de responsabilidades:
* **Camada de Interface / Controllers (`app/Controllers/Api/`):** Validação de payloads HTTP, headers, autorização e padronização de respostas JSON através de helpers (`script_helper`).
* **Camada de Domínio e Persistência (`app/Models/`):** Transações atômicas com `$this->db->transStart()`, cálculo de totais, baixa de estoque físico, integridade referencial e disparo da trilha de auditoria.
* **Infraestrutura & Banco de Dados (`app/Database/`):** Migrações versionadas e seeders para reprodução idêntica do ambiente.

---

## 🛡️ 7. Declaração Obrigatória de Uso de IA

Em estrita conformidade com as diretrizes avaliativas da disciplina de Projeto Multidisciplinar (ano 2026), declara-se que ferramentas de inteligência artificial generativa (Google Antigravity / Gemini 3.8) foram utilizadas exclusivamente como recurso auxiliar de suporte ao estudo, revisão gramatical/ortográfica, estruturação documental segundo as normas da ABNT e consulta de boas práticas da arquitetura REST, sem substituição do raciocínio analítico, tomada de decisão técnica ou autoria do estudante. Toda a implementação em código-fonte, migrações, models, controladores e testes foi executada pelo aluno conforme histórico do Git.
