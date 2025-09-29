# UI Modify - Zabbix Frontend Module

**Versão:** 1.0.0
**Compatibilidade:** Zabbix 6.0+
**Licença:** GNU AGPLv3

## Descrição

Módulo de frontend para personalização da interface do Zabbix que permite:

- ✅ Upload e gerenciamento de logos customizados
- ✅ Suporte completo a todos os temas do Zabbix (Blue, Dark, High-Contrast Light/Dark)
- ✅ Validação de dimensões de imagem em tempo real
- ✅ Preview antes do upload
- ✅ Geração automática do arquivo `brand.conf.php`
- ✅ Customização de footer e URL de ajuda
- ✅ Interface responsiva e user-friendly

## Estrutura do Módulo

```
uimodify/
├── manifest.json                          # Manifesto do módulo
├── Module.php                             # Classe principal do módulo
├── actions/                               # Controllers
│   ├── CControllerUIModifyLogoEdit.php   # Exibição do formulário
│   ├── CControllerUIModifyLogoUpload.php # Upload de logos
│   ├── CControllerUIModifyLogoPreview.php# Preview de imagens
│   └── CControllerUIModifyConfigSave.php # Salvar configurações
├── views/                                 # Views
│   └── uimodify.logo.edit.php           # Formulário de upload
├── assets/                                # Assets estáticos
│   ├── css/
│   │   └── uimodify.css                 # CSS com suporte a temas
│   └── js/
│       └── uimodify.js                  # JavaScript de validação
└── storage/                               # Armazenamento temporário
```

## Instalação

### 1. Copiar o módulo

```bash
sudo cp -r uimodify /usr/share/zabbix/modules/
```

### 2. Criar diretórios necessários

```bash
sudo mkdir -p /usr/share/zabbix/local/conf
sudo mkdir -p /usr/share/zabbix/rebranding
sudo mkdir -p /usr/share/zabbix/modules/uimodify/storage
```

### 3. Configurar permissões

```bash
sudo chown -R www-data:www-data /usr/share/zabbix/modules/uimodify
sudo chown -R www-data:www-data /usr/share/zabbix/local
sudo chown -R www-data:www-data /usr/share/zabbix/rebranding

sudo chmod 755 /usr/share/zabbix/modules/uimodify
sudo chmod 755 /usr/share/zabbix/local/conf
sudo chmod 755 /usr/share/zabbix/rebranding
```

### 4. Ativar o módulo

1. Acesse o Zabbix frontend como Super Admin
2. Vá em: **Administration → General → Modules**
3. Clique em **Scan directory**
4. Encontre "UI Modify" na lista
5. Clique em **Enable**

### 5. Acessar o módulo

Após ativado, acesse: **Administration → UI Customization**

## Uso

### Dimensões Recomendadas de Logos

| Tipo | Dimensões | Uso |
|------|-----------|-----|
| **Main Logo** | 114×30 px | Tela de login |
| **Sidebar Logo** | 91×24 px | Menu lateral expandido |
| **Compact Logo** | 24×24 px | Menu lateral compacto (ícone) |

### Formatos Suportados

- SVG (recomendado para qualidade em qualquer resolução)
- PNG (com transparência)
- JPG/JPEG
- GIF
- WebP

### Workflow de Upload

1. **Selecione o tipo de logo** (Main, Sidebar ou Compact)
2. **Escolha o arquivo** (até 5MB)
3. **Visualize o preview** com validação de dimensões
4. **Configure footer e URL de ajuda** (opcional)
5. **Clique em "Upload Logo"**
6. **Recarregue a página** para ver as alterações

## Compatibilidade com Temas

O módulo utiliza CSS variables para suporte automático a todos os temas:

### Blue Theme (Padrão)
- Esquema de cores azul tradicional
- Boa legibilidade e contraste

### Dark Theme
- Fundo escuro para reduzir cansaço visual
- Cores ajustadas automaticamente

### High-Contrast Light
- Alto contraste para acessibilidade
- Bordas e textos em preto

### High-Contrast Dark
- Alto contraste com fundo escuro
- Ideal para deficiências visuais

## Arquivo Gerado

O módulo gera automaticamente `/usr/share/zabbix/local/conf/brand.conf.php`:

```php
<?php
return [
	'BRAND_LOGO' => './rebranding/logo_main_20250929120000.svg',
	'BRAND_LOGO_SIDEBAR' => './rebranding/logo_sidebar_20250929120000.svg',
	'BRAND_LOGO_SIDEBAR_COMPACT' => './rebranding/logo_compact_20250929120000.svg',
	'BRAND_FOOTER' => 'Your Company Name',
	'BRAND_HELP_URL' => 'https://yourcompany.com/help',
];
```

## Funcionalidades JavaScript

### Validação em Tempo Real
- Verifica tamanho do arquivo (máx. 5MB)
- Valida extensões permitidas
- Confere dimensões da imagem

### Sistema de Alertas
- ✅ **Success:** Dimensões perfeitas
- ⚠️ **Warning:** Dimensões próximas mas não exatas
- ❌ **Error:** Arquivo inválido ou dimensões muito diferentes

### Preview Instantâneo
- Visualização antes do upload
- Exibição de dimensões reais vs. esperadas
- Suporte a todos os formatos de imagem

## Troubleshooting

### O módulo não aparece na lista

**Solução:**
```bash
sudo chown -R www-data:www-data /usr/share/zabbix/modules/uimodify
sudo chmod 755 /usr/share/zabbix/modules/uimodify
```

### Erro ao fazer upload

**Verifique permissões:**
```bash
ls -la /usr/share/zabbix/rebranding/
ls -la /usr/share/zabbix/local/conf/
```

**Corrija se necessário:**
```bash
sudo chown www-data:www-data /usr/share/zabbix/rebranding/
sudo chown www-data:www-data /usr/share/zabbix/local/conf/
```

### Logo não aparece após upload

1. Limpe o cache do navegador (Ctrl+F5)
2. Verifique se o arquivo foi criado: `ls -la /usr/share/zabbix/rebranding/`
3. Verifique o conteúdo de: `cat /usr/share/zabbix/local/conf/brand.conf.php`
4. Certifique-se que o servidor web tem permissão de leitura

### CSS não está sendo aplicado

Verifique se os assets foram carregados corretamente:
```bash
ls -la /usr/share/zabbix/modules/uimodify/assets/css/
ls -la /usr/share/zabbix/modules/uimodify/assets/js/
```

## Arquitetura Técnica

### Controllers (MVC Pattern)

- **CControllerUIModifyLogoEdit**: Renderiza formulário de gerenciamento
- **CControllerUIModifyLogoUpload**: Processa upload e atualiza configuração
- **CControllerUIModifyLogoPreview**: Gera preview base64 da imagem
- **CControllerUIModifyConfigSave**: Salva configurações de footer/help URL

### Security Features

- ✅ Verificação de permissões (apenas Super Admin)
- ✅ Validação de extensões de arquivo
- ✅ Validação de tamanho de arquivo
- ✅ Sanitização de inputs
- ✅ CSRF protection via Zabbix framework

## Desenvolvimento

### Adicionar Novos Tipos de Logo

1. Edite `CControllerUIModifyLogoEdit.php`:
```php
private const LOGO_DIMENSIONS = [
    'main' => ['width' => 114, 'height' => 30, 'label' => 'Main Logo'],
    'custom' => ['width' => 200, 'height' => 50, 'label' => 'Custom Logo'] // Novo
];
```

2. Atualize o JavaScript em `uimodify.js`:
```javascript
dimensions: {
    custom: { width: 200, height: 50, label: 'Custom Logo' }
}
```

3. Adicione ao `brand.conf.php` mapping no `updateBrandConfig()`.

### Customizar Temas

Edite `/usr/share/zabbix/modules/uimodify/assets/css/uimodify.css`:

```css
[data-theme="your-custom-theme"] .logo-preview-box {
    --ui-border-color: #yourcolor;
    --ui-bg-color: #yourcolor;
}
```

## Referências

- [Zabbix Frontend Modules Documentation](https://www.zabbix.com/documentation/current/en/devel/modules)
- [Zabbix Rebranding Guide](https://www.zabbix.com/documentation/current/en/manual/web_interface/rebranding)
- [initMAX Zabbix UI Rebranding Skeleton](https://git.initmax.cz/initMAX-Public/Zabbix-UI-Rebranding-skeleton)

## Contribuição

Contribuições são bem-vindas! Por favor:

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## Licença

GNU Affero General Public License v3.0

## Autor

Módulo desenvolvido com base nas melhores práticas do Zabbix e inspirado nos projetos:
- initMAX Zabbix UI Rebranding Skeleton
- initMAX UX MAX Modules

---

**Versão:** 1.0.0 | **Data:** 2025-09-29