# Evidencias de Testes

Data da execucao: 2026-05-25  
Projeto: Sistema Financeiro Interno - Controle de Caixa  
Executor: Codex

## Escopo validado

Como nao havia criterios de aceitacao formais no repositorio, a validacao foi baseada nos fluxos criticos mapeados em `routes/web.php`, controllers, requests e views:

- Login e bloqueio de usuario.
- Abertura e fechamento de caixa.
- Registro de entradas e saidas.
- Calculo de saldo consolidado.
- Extrato e exportacao CSV com filtros.
- Gestao de usuarios restrita a administradores.
- Build dos assets front-end.

## Testes automatizados executados

Comando principal:

```powershell
php tests\run.php
```

Resultado:

```text
PASS unit: User identifica perfil admin e conta bloqueada
PASS unit: regras de validacao de abertura de caixa
PASS unit: regras de validacao de lancamento financeiro
PASS integration: login bloqueia usuario inativo
PASS integration: admin abre caixa e duplicidade e impedida
PASS integration: lancamentos alteram saldo e fechamento consolida caixa
PASS integration: exportacao CSV respeita filtros
PASS integration: admin cria usuario e operador nao acessa gestao

8 testes executados, 0 falha(s).
```

### Cobertura

| ID | Tipo | Cenario | Resultado |
| --- | --- | --- | --- |
| TU-01 | Unidade | `User::isAdmin()` e `User::isBlocked()` retornam o perfil/status correto. | Aprovado |
| TU-02 | Unidade | `OpenCashRegisterRequest` exige saldo inicial numerico e nao negativo. | Aprovado |
| TU-03 | Unidade | `StoreTransactionRequest` valida tipo, valor positivo e comprovante PNG/JPG/PDF ate 10 MB. | Aprovado |
| TI-01 | Integracao | Login de usuario bloqueado retorna erro e nao autentica. | Aprovado |
| TI-02 | Integracao | Admin abre caixa e o sistema impede segundo caixa aberto. | Aprovado |
| TI-03 | Integracao | Lancamentos de entrada/saida sao gravados e fechamento calcula saldo final. | Aprovado |
| TI-04 | Integracao | Exportacao CSV respeita filtros de tipo e busca. | Aprovado |
| TI-05 | Integracao | Admin cria usuario; operador recebe 403 ao acessar gestao de usuarios. | Aprovado |

## Outras verificacoes executadas

| Comando | Resultado | Observacao |
| --- | --- | --- |
| `php -l tests\run.php` | Aprovado | Sem erros de sintaxe. |
| `php vendor\bin\pint --test tests\run.php` | Aprovado | Arquivo segue o padrao do Pint. |
| `php artisan route:list` | Aprovado | 21 rotas carregadas corretamente. |
| `& 'C:\Users\Pedro\.cache\codex-runtimes\codex-primary-runtime\dependencies\node\bin\node.exe' node_modules\vite\bin\vite.js build` | Aprovado | Build Vite gerou `manifest.json`, CSS e JS em `public/build`. |

## Comandos nao aplicaveis ou bloqueados

| Comando | Resultado | Motivo |
| --- | --- | --- |
| `php artisan test` | Bloqueado | O projeto nao possui PHPUnit instalado; o comando falha com `Class "SebastianBergmann\Environment\Console" not found`. A suite local em `tests/run.php` foi criada para validar unidade/integracao sem baixar dependencias externas. |
| `npm run build` | Bloqueado | `npm` nao esta disponivel no PATH deste ambiente. O build foi validado chamando o Vite diretamente pelo Node empacotado do Codex. |
| Servidor local para teste manual via navegador | Bloqueado | O ambiente rejeitou a tentativa de iniciar processo local em segundo plano; por isso a evidencia de browser ficou pendente. |

## Testes manuais documentados

Os passos abaixo documentam a execucao manual esperada para validacao final em navegador. Nesta rodada, os mesmos fluxos foram exercitados pela suite de integracao aprovada acima; a execucao clicada em browser deve ser repetida quando o ambiente permitir iniciar o servidor local.

| ID | Fluxo manual | Passos | Resultado esperado | Evidencia nesta rodada | Status |
| --- | --- | --- | --- | --- | --- |
| TM-01 | Login administrativo | Acessar `/login`, informar `admin@example.com` e `password`, enviar formulario. | Usuario autenticado e redirecionado ao painel. | Login admin usado nos testes TI-02 a TI-05. | Coberto por integracao |
| TM-02 | Bloqueio de usuario | Tentar login com usuario marcado como `bloqueado`. | Sistema exibe erro de conta bloqueada e nao autentica. | TI-01 aprovado. | Coberto por integracao |
| TM-03 | Abertura de caixa | Com admin logado, acionar abertura de caixa com saldo inicial. | Caixa aberto, dashboard exibe saldo inicial e botao de novo lancamento fica disponivel. | TI-02 aprovado. | Coberto por integracao |
| TM-04 | Impedir caixa duplicado | Tentar abrir novo caixa enquanto ja existe um caixa `aberto`. | Sistema impede duplicidade e mantem apenas um caixa aberto. | TI-02 aprovado. | Coberto por integracao |
| TM-05 | Registro de lancamentos | Registrar uma entrada e uma saida com descricao, categoria, competencia e observacoes. | Lancamentos persistidos e considerados no saldo. | TI-03 aprovado. | Coberto por integracao |
| TM-06 | Fechamento de caixa | Fechar caixa apos lancamentos. | Caixa muda para `fechado` e saldo final = saldo inicial + entradas - saidas. | TI-03 aprovado. | Coberto por integracao |
| TM-07 | Extrato e CSV | Filtrar extrato por tipo/busca e exportar CSV. | CSV contem apenas registros compativeis com os filtros. | TI-04 aprovado. | Coberto por integracao |
| TM-08 | Gestao de usuarios | Criar operador como admin; tentar acessar `/usuarios` como operador. | Admin cria usuario; operador recebe acesso negado. | TI-05 aprovado. | Coberto por integracao |
| TM-09 | Assets front-end | Executar build e abrir telas principais com CSS/JS carregados. | Manifest, CSS e JS gerados; telas sem erro de asset. | Build Vite aprovado. | Build aprovado; browser pendente |

## Conclusao

Os testes de unidade e integracao aplicaveis foram criados e executados com sucesso: 8 testes aprovados e 0 falhas. Os testes manuais foram documentados com passos, resultados esperados e evidencia equivalente por integracao. A validacao visual clicada em navegador deve ser feita em ambiente que permita iniciar o servidor local.
