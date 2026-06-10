# Contribuindo com o JusAI

## Fluxo de trabalho

1. **Abra uma issue** antes de começar — descreva o que vai fazer e por quê
2. **Crie uma branch** a partir de `main` com o padrão `tipo/descricao-curta`
   ```
   feat/chat-historico-exportar
   fix/modal-documento-pdf
   ai/prompt-revisao-minuta
   ```
3. **Faça commits** atômicos com mensagens descritivas:
   ```
   feat: exportar histórico de chat em PDF
   fix: fechar modal de documento ao pressionar Esc
   ai: refinar prompt de revisão de minuta para CLT
   ```
4. **Abra uma Pull Request** referenciando a issue (`Closes #123`)
5. Aguarde review antes do merge em `main`

## Padrões de código

- PHP: PSR-12, formatado com `./vendor/bin/pint`
- Blade/Livewire: componentes reutilizáveis em `resources/views/components`
- Sempre rodar `npm run build` após editar JS ou CSS
- Sem credenciais, chaves de API ou dados de clientes em commits

## Rodando os linters

```bash
./vendor/bin/pint          # formata PHP
php artisan test           # testes unitários
```

## Prompts de IA

Os prompts jurídicos ficam em `config/ai_prompts.php` (defaults) e na tabela `ai_prompts` (overrides em runtime via painel admin). Para sugerir melhorias nos prompts, abra uma issue com o template **Melhoria de IA** — inclua um exemplo anonimizado do comportamento atual e do esperado.

## Segurança

Encontrou uma vulnerabilidade? **Não abra uma issue pública.** Envie para joao@maihub.io com o assunto `[SECURITY] JusAI`.
