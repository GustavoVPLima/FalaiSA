# ⚡ INÍCIO RÁPIDO - PHP Puro

## 5 Minutos para Começar

### 1️⃣ Copiar Estrutura
```bash
# Copiar pasta falai-php para seu servidor
cd /var/www/html
cp -r falai-php ./seu-projeto
cd seu-projeto
```

### 2️⃣ Configurar Banco
```bash
# Abrir e atualizar credenciais
nano config/database.php
```

**Seu BD ja existe?** Copie apenas as credenciais:
```php
return [
    'host' => 'seu_host',
    'user' => 'seu_usuario',
    'password' => 'sua_senha',
    'database' => 'seu_banco'
];
```

### 3️⃣ Copiar seus Arquivos Originais
```bash
# Copiar static (CSS, JS, imagens, uploads)
cp -r ../seu-projeto-flask/static ./

# Criar permissões de upload
chmod 755 static/uploads
chmod 755 static/uploads/*
```

### 4️⃣ Converter Templates
Pegar cada arquivo HTML do projeto Flask e:

1. Renomear `.html` → `.php`
2. Copiar para pasta `templates/`
3. Converter sintaxe:

**Antes (Jinja2 - Flask):**
```html
<h1>{{ titulo }}</h1>
{% for item in items %}
  <p>{{ item.nome }}</p>
{% endfor %}
```

**Depois (PHP):**
```php
<h1><?php echo htmlspecialchars($titulo); ?></h1>
<?php foreach ($items as $item): ?>
  <p><?php echo htmlspecialchars($item['nome']); ?></p>
<?php endforeach; ?>
```

### 5️⃣ Testar
```bash
# Apache ja está rodando?
sudo systemctl restart apache2

# Acessar no navegador
http://localhost/seu-projeto
```

---

## 🎯 Checklist Básico

- [ ] Copiar estrutura PHP
- [ ] Configurar credenciais BD em `config/database.php`
- [ ] Testar conexão: acessar `http://localhost/seu-projeto/login`
- [ ] Copiar `static/` do Flask
- [ ] Converter templates HTML → PHP
- [ ] Testar login
- [ ] Testar criar comunidade
- [ ] Testar chat

---

## 📚 Arquivos Importantes para Ler

**Pela ordem:**
1. `README.md` - Visão geral
2. `ESTRUTURA.md` - Entender a organização
3. `MIGRATION_GUIDE.md` - Converter código
4. `FAQ.md` - Solucionar problemas
5. `DEPLOYMENT.md` - Colocar em produção

---

## 🚀 Seu Fluxo

```
┌─────────────────┐
│ Projeto Flask   │
└────────┬────────┘
         │ copiar static/
         ↓
┌─────────────────┐     ┌──────────────────┐
│  Converter HTML │────→│ Testar templates │
│ HTML → PHP      │     └──────────────────┘
└────────┬────────┘
         │
         ↓
┌─────────────────┐     ┌──────────────────┐
│ Testar rotas    │────→│ Sucesso! Deploy  │
│ /login, /chat   │     └──────────────────┘
└─────────────────┘
```

---

## 🆘 Problemas Comuns no Início

### "404 em /login"
```bash
# Ativar mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### "Erro ao conectar BD"
```bash
# Testar credenciais
mysql -h seu_host -u usuario -p banco_dados

# Atualizar em config/database.php
```

### "Templates não renderizam"
- Verificar se `.php` está em `templates/`
- Usar `View::show('login')` sem `.php`

### "Uploads não funcionam"
```bash
mkdir -p static/uploads/{usuarios,comunidades,chat}
chmod 777 static/uploads
```

---

## ✅ Próximos Passos Após Básico

1. **Converter todos os templates** de `templates/*.php`
2. **Testar funcionalidades** uma por uma
3. **Adicionar CSRF tokens** (veja em `FAQ.md`)
4. **Configurar HTTPS** em produção
5. **Deploy** (veja em `DEPLOYMENT.md`)

---

## 💡 Dica Pro

**Você pode ter Flask e PHP rodando simultaneamente!**

```bash
# Terminal 1 - Flask (porta 5000)
cd seu-projeto-flask
python app.py

# Terminal 2 - PHP (porta 80 com Apache)
# Já roda automaticamente
```

Para testar migrações gradualmente sem quebrar o Flask original.

---

## 📞 Precisa de Ajuda?

1. **Erro específico?** → Procure em `FAQ.md`
2. **Não sabe converter código?** → Veja `MIGRATION_GUIDE.md`
3. **Quer exemplo?** → Abra `src/Controllers/` ou `exemplo-rotas.php`
4. **Quer hospedar?** → Leia `DEPLOYMENT.md`

---

**Comece agora! Qualquer dúvida, releia os guias acima.** 🚀
