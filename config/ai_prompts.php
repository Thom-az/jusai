<?php

return [

    'system' => [

        'base' => <<<'PROMPT'
Você é um assistente jurídico especializado no ordenamento jurídico brasileiro. Opera como copiloto de escritório de advocacia.

Princípios obrigatórios:
- Baseie-se sempre em legislação vigente: CF/88, CPC/2015, CLT, Código Civil/2002, CDC (Lei 8.078/90), Estatuto da OAB (Lei 8.906/94) e demais normas aplicáveis ao caso.
- Cite jurisprudência (STF, STJ, TRT, TJ) somente quando estiver presente nos documentos fornecidos ou quando for súmula vinculante/tese de repercussão geral amplamente conhecida.
- NUNCA invente artigos de lei, números de processo, nomes de ministros ou datas de julgamentos não mencionados nos documentos.
- Sempre conclua indicando que o conteúdo deve ser revisado por advogado habilitado pela OAB.
- Idioma: português do Brasil, norma culta, linguagem técnica jurídica.
PROMPT,

        'resumo_caso' => <<<'PROMPT'
Produza um resumo executivo estruturado em exatamente 4 seções numeradas:

1. **Síntese do Caso** — resumo objetivo dos fatos relevantes, pretensão das partes e fase processual atual
2. **Partes Envolvidas** — qualificação das partes (nome/denominação, papel processual: autor/réu/interveniente/terceiro)
3. **Objeto da Disputa/Operação** — descrição precisa do pedido ou operação jurídica, com valores e prazos se informados
4. **Pontos de Atenção Prioritários** — liste os riscos, prazos críticos e ações imediatas em ordem de urgência. Use ⚠️ para urgente e 📋 para ação necessária.

Seja conciso e direto. Máximo de 600 palavras no total.
PROMPT,

        'analise_documento' => <<<'PROMPT'
Analise o documento jurídico e estruture a resposta em 4 seções:

1. **Cláusulas-Chave** — liste e explique as cláusulas mais relevantes, citando o número ou título de cada uma
2. **Riscos Identificados** — para cada risco: descrição, classificação (🔴 Alto / 🟡 Médio / 🟢 Baixo) e fundamento legal aplicável (ex: art. 51, IV do CDC; art. 413 do CC/2002)
3. **Prazos Relevantes** — todos os prazos mencionados no documento: vigência, notificação, entrega, vencimentos
4. **Recomendações Práticas** — ações concretas e objetivas para mitigar os riscos identificados, em ordem de prioridade

Fundamente em: Código Civil/2002, CPC/2015, CDC (Lei 8.078/90), CLT e demais legislações aplicáveis ao tipo de documento analisado.
PROMPT,

        'revisao_minuta' => <<<'PROMPT'
Revise a minuta jurídica e identifique problemas em 4 categorias:

1. **Inconsistências Jurídicas** — cláusulas que contradizem a legislação vigente; cite a norma violada e proponha correção objetiva
2. **Ambiguidades e Lacunas** — trechos com redação ambígua ou omissões que possam gerar litígio; cite o trecho original e sugira redação alternativa
3. **Ausências Relevantes** — cláusulas que deveriam constar mas estão ausentes (ex: foro de eleição, caso fortuito/força maior, LGPD se houver dados pessoais, multa rescisória, garantias)
4. **Sugestões de Melhoria** — melhorias de clareza, precisão técnica e segurança jurídica sem alterar a intenção das partes

Verifique conformidade com: CC/2002 (arts. 104, 421-425), CDC se contrato de consumo (arts. 46-54), CLT se contrato de trabalho, LGPD (Lei 13.709/18) para dados pessoais, e normas específicas ao tipo de documento.
PROMPT,

        'pesquisa_juridica' => <<<'PROMPT'
Com base nos documentos do caso fornecidos, responda à questão jurídica com fundamentação técnica estruturada:

1. **Síntese da Questão** — reformule a pergunta em termos jurídicos precisos
2. **Fundamento Legal** — cite os artigos de lei aplicáveis (CF/88, CPC/2015, CC/2002, CLT, legislação especial conforme a matéria)
3. **Análise** — desenvolva o raciocínio jurídico articulando os fundamentos ao caso concreto
4. **Jurisprudência Aplicável** — cite súmulas ou teses do STF/STJ APENAS se presentes nos documentos do caso ou se forem súmulas vinculantes/teses de repercussão geral amplamente conhecidas
5. **Conclusão** — resposta objetiva com grau de segurança jurídica: Alta (legislação clara), Média (matéria controvertida) ou Baixa (ausência de base sólida)

Se a questão não puder ser respondida com os documentos disponíveis, informe claramente o que falta para uma análise adequada.
PROMPT,

        'rascunho_minuta' => <<<'PROMPT'
Você é um assistente jurídico especializado em redação de peças e documentos jurídicos brasileiros. Gere o documento solicitado com rigor técnico.

Padrões obrigatórios:
- Linguagem jurídica formal, norma culta, pronomes de tratamento corretos (Excelentíssimo Senhor Juiz, Vossa Excelência, etc.)
- Qualificação completa das partes quando disponível (nome, CPF/CNPJ, endereço, representante legal)
- Numeração sequencial de tópicos, parágrafos e alíneas quando pertinente
- Cite o fundamento legal de cada pedido ou cláusula relevante (artigo e lei)
- Use [PREENCHER: descrição do dado necessário] onde informações não foram fornecidas
- Finalize com local e data ([LOCAL], [DATA]) e espaço para assinatura do advogado com OAB

Peculiaridades por tipo de documento:
- **Petição Inicial:** Endereçamento ao juízo, qualificação das partes, fatos, fundamentos (art. 319 CPC/2015), pedidos, valor da causa
- **Contestação:** Preliminares (se cabíveis), impugnação específica dos fatos, fundamentos, pedido de improcedência (art. 335 CPC/2015)
- **Recurso:** Cabeçalho ao tribunal, dados do processo, razões recursais com referência à decisão recorrida, pedido de reforma ou anulação
- **Notificação Extrajudicial:** Qualificação do notificante/notificado, fato gerador, exigência específica, prazo para cumprimento, consequências do descumprimento
- **Contrato:** Qualificação completa, objeto, obrigações recíprocas, prazo, valor e pagamento, penalidades, rescisão, foro

Gere o documento completo e bem estruturado com base nas instruções recebidas.
PROMPT,

        'chat' => <<<'PROMPT'
Você é o assistente jurídico do escritório, especializado no ordenamento jurídico brasileiro. Está respondendo perguntas sobre um caso jurídico específico.

Regras de comportamento:
- Responda de forma direta e objetiva, em linguagem técnica jurídica brasileira
- Ao citar legislação, indique sempre o artigo e a lei (ex: "art. 319 do CPC/2015")
- Nunca invente fatos, leis, artigos ou jurisprudências
- Se não tiver informação suficiente no contexto do caso, diga claramente e oriente o que buscar
- Mantenha coerência com o histórico da conversa
- Alerte quando uma questão exigir consulta a especialista ou pesquisa mais aprofundada
PROMPT,

    ],

    'mock' => [

        'resumo_caso' => <<<'MOCK'
## Resumo Executivo

**1. Síntese do Caso**
Ação de cobrança por inadimplemento contratual, ajuizada perante a 3ª Vara Cível da Comarca de São Paulo/SP. A parte autora pleiteia o pagamento de valores devidos em razão de prestação de serviços não adimplidos pela parte ré. O feito encontra-se na fase de instrução probatória, aguardando designação de audiência de instrução e julgamento.

**2. Partes Envolvidas**
- **Requerente (Autor):** Empresa Prestadora de Serviços Ltda. — representada nos autos por advogado constituído
- **Requerido (Réu):** Empresa Contratante S.A. — citada regularmente, prazo para contestação em curso

**3. Objeto da Disputa**
Cobrança de parcelas contratuais vencidas e não pagas, no valor total de R$ 85.000,00 (oitenta e cinco mil reais), acrescidas de multa contratual de 2% e juros moratórios de 1% ao mês a partir do vencimento de cada parcela, conforme cláusula 12ª do contrato de prestação de serviços firmado entre as partes.

**4. Pontos de Atenção Prioritários**
⚠️ **Urgente** — Prazo para apresentação de contestação expira em 15 dias úteis (art. 335 do CPC/2015); verificar data de citação nos autos
⚠️ **Urgente** — Avaliar tutela de urgência para indisponibilidade de bens (art. 300 do CPC/2015) se houver risco de insolvência do réu
📋 **Ação necessária** — Reunir todos os comprovantes de prestação dos serviços para instrução do feito
📋 **Verificar** — Prazo prescricional: ações pessoais prescrevem em 5 anos (art. 206, §5º, I do Código Civil/2002)

---
*⚠️ Resultado de demonstração — AI_PROVIDER=mock. Configure ANTHROPIC_API_KEY e AI_PROVIDER=anthropic para análise real.*
MOCK,

        'analise_documento' => <<<'MOCK'
## Análise do Documento Jurídico

**1. Cláusulas-Chave Identificadas**
- **Cláusula 3ª — Objeto:** Define o escopo dos serviços; a redação ampla pode gerar conflito interpretativo sobre o que está incluído na contraprestação.
- **Cláusula 7ª — Vigência:** Prazo determinado de 12 meses com renovação automática sem exigência de notificação prévia — risco de vinculação involuntária das partes.
- **Cláusula 12ª — Penalidades:** Multa moratória de 2% + juros de 1% a.m. sobre o valor inadimplido; compatível com o art. 52, §1º do CDC se contrato de consumo.
- **Cláusula 18ª — Foro:** Eleição de foro na Comarca de São Paulo/SP; verificar aplicabilidade em eventual contrato de adesão (art. 63, §3º do CPC/2015).

**2. Riscos Identificados**
| Risco | Classificação | Fundamento Legal |
|---|---|---|
| Renovação automática sem aviso prévio | 🔴 Alto | Art. 51, IV do CDC |
| Ausência de cláusula de força maior/caso fortuito | 🟡 Médio | Art. 393 do CC/2002 |
| Foro de eleição em possível contrato de adesão | 🟡 Médio | Art. 112 e 63, §3º do CPC/2015 |
| Multa unilateral aplicável somente ao devedor | 🟢 Baixo | Art. 413 do CC/2002 |

**3. Prazos Relevantes**
- Vigência contratual: 12 meses a partir da assinatura
- Notificação prévia para rescisão: 30 dias de antecedência (Cláusula 9ª)
- Vencimento das parcelas mensais: todo dia 5 do mês subsequente à prestação

**4. Recomendações Práticas**
1. Negociar inclusão de cláusula de rescisão por mútuo acordo sem penalidade (art. 472 do CC/2002)
2. Revisar cláusula de renovação automática para exigir notificação expressa com antecedência mínima de 30 dias
3. Incluir cláusula de força maior e caso fortuito (art. 393 do CC/2002) com definição objetiva dos eventos excludentes
4. Avaliar adequação do foro eleito: se contrato de adesão firmado com consumidor, pode ser declarado nulo (art. 51, IV do CDC)

---
*⚠️ Resultado de demonstração — AI_PROVIDER=mock. Configure ANTHROPIC_API_KEY e AI_PROVIDER=anthropic para análise real.*
MOCK,

        'revisao_minuta' => <<<'MOCK'
## Revisão da Minuta Jurídica

**1. Inconsistências Jurídicas**
- **Cláusula 5ª — Remuneração:** A previsão de atualização monetária pelo IGP-M é válida para contratos B2B (art. 316 do CC/2002), porém em contratos com consumidores o índice não pode divergir da inflação oficial (IPCA) sob pena de abusividade (art. 51, IV do CDC). Sugestão: distinguir o indexador conforme a natureza do contratante.
- **Cláusula 11ª — Propriedade Intelectual:** A cessão de direitos autorais deve ser expressa e com remuneração específica (art. 49 da Lei 9.610/98). A redação atual não menciona contraprestação, tornando a cláusula potencialmente ineficaz.

**2. Ambiguidades e Lacunas**
- **Cláusula 3ª, §2º:** O trecho *"serviços complementares quando necessários"* é indeterminado e pode gerar conflito sobre o que está incluído no preço. Sugestão: listar taxativamente os serviços complementares ou estabelecer procedimento de aprovação prévia com registro escrito.
- **Ausência de SLA:** O contrato não define prazo de entrega ou nível mínimo de qualidade, impossibilitando a aferição objetiva do inadimplemento parcial.

**3. Ausências Relevantes**
- **Foro de eleição:** Não há cláusula definindo o juízo competente para litígios. Recomendado incluir (art. 63 do CPC/2015).
- **LGPD:** O contrato envolve dados pessoais de clientes, mas não há cláusula de proteção de dados, definição de controlador/operador, nem obrigações específicas conforme a Lei 13.709/18.
- **Rescisão por justa causa:** Ausência de hipóteses objetivas de rescisão imediata sem multa (ex: insolvência, descumprimento reiterado, força maior).

**4. Sugestões de Melhoria**
- Substituir *"as partes concordam"* por *"as partes obrigam-se a"* em toda a minuta para reforço do caráter obrigacional.
- Incluir glossário no preâmbulo com definições dos termos técnicos recorrentes (*"Serviços", "Entregáveis", "Dados Pessoais"*).
- Adicionar cláusula de independência das partes para contratos de prestação de serviços, prevenindo reconhecimento de vínculo empregatício (art. 442-B da CLT).

---
*⚠️ Resultado de demonstração — AI_PROVIDER=mock. Configure ANTHROPIC_API_KEY e AI_PROVIDER=anthropic para análise real.*
MOCK,

        'pesquisa_juridica' => <<<'MOCK'
## Pesquisa Jurídica

**1. Síntese da Questão**
Análise da possibilidade de rescisão contratual por inadimplemento com aplicação cumulada de multa compensatória e multa moratória no direito contratual brasileiro.

**2. Fundamento Legal**
- **Art. 394 do CC/2002:** Define mora como o retardamento culposo no cumprimento da obrigação.
- **Art. 395 do CC/2002:** O devedor em mora responde pelos prejuízos causados, acrescidos de juros e atualização monetária.
- **Arts. 408 e 409 do CC/2002:** A cláusula penal pode ser compensatória (substitui perdas e danos) ou moratória (acumula-se à obrigação principal) — ambas podem coexistir.
- **Art. 412 do CC/2002:** A cláusula penal não pode exceder o valor da obrigação principal.
- **Art. 475 do CC/2002:** A parte lesada pode exigir a resolução do contrato ou o cumprimento, com perdas e danos.

**3. Análise**
A distinção entre multa moratória e compensatória é fundamental: a moratória incide durante o inadimplemento e não exclui a rescisão; a compensatória, por sua vez, substitui as perdas e danos decorrentes da rescisão contratual. A jurisprudência do STJ orienta-se no sentido de que as duas espécies de multa podem coexistir quando expressamente previstas no instrumento, aplicando-se o limite do art. 412 do CC/2002 separadamente a cada uma delas.

**4. Jurisprudência Aplicável**
Para a cumulação de multas em contratos empresariais, verificar os precedentes da 3ª e 4ª Turmas do STJ. A Súmula 616 do STJ não é diretamente aplicável a este caso. Recomenda-se pesquisa atualizada no portal do STJ (stj.jus.br) para jurisprudência específica ao tipo contratual envolvido.

**5. Conclusão**
Grau de segurança jurídica: **Média** — A cumulação é juridicamente possível quando expressamente prevista em contrato (arts. 408 e 409 do CC/2002), porém há divergência sobre os limites do art. 412. Recomenda-se análise do contrato específico e consulta à jurisprudência atualizada do STJ antes de qualquer decisão processual.

---
*⚠️ Resultado de demonstração — AI_PROVIDER=mock. Configure ANTHROPIC_API_KEY e AI_PROVIDER=anthropic para análise real.*
MOCK,

        'rascunho_minuta' => <<<'MOCK'
EXCELENTÍSSIMO(A) SENHOR(A) DOUTOR(A) JUIZ(A) DE DIREITO DA [VARA/JUÍZO] DA COMARCA DE [CIDADE]/[ESTADO]

Processo nº: [PREENCHER: número do processo, se já existente]

**[NOME DO AUTOR/REQUERENTE]**, [nacionalidade], [estado civil], [profissão], portador(a) do CPF nº [CPF] e RG nº [RG], residente e domiciliado(a) na [endereço completo, CEP], por intermédio de seu(ua) advogado(a) que esta subscreve (OAB/[UF] nº [número]), com escritório profissional na [endereço do advogado], onde recebe intimações, vem, respeitosamente, à presença de Vossa Excelência, propor a presente

---

**AÇÃO DE [TIPO DA AÇÃO]**

em face de

**[NOME DO RÉU/REQUERIDO]**, [qualificação completa ou CNPJ], com sede/domicílio em [endereço], pelos fatos e fundamentos a seguir expostos:

---

**I — DOS FATOS**

[PREENCHER: Descreva os fatos de forma cronológica e objetiva. Inclua: datas, valores, documentos que amparam o pedido, tentativas de resolução extrajudicial e descumprimento da parte contrária.]

**II — DO DIREITO**

[PREENCHER: Fundamente o pedido com os artigos de lei aplicáveis. Ex: "Nos termos do art. [número] do [Código/Lei]..." Cite ao menos 2-3 dispositivos legais pertinentes.]

**III — DOS PEDIDOS**

Diante do exposto, requer a Vossa Excelência:

a) O recebimento e processamento da presente ação, determinando-se a citação do(a) Réu(é) no endereço supra, para que, querendo, apresente contestação no prazo legal, sob pena de revelia;

b) A procedência dos pedidos para condenar o(a) Réu(é) a [PREENCHER: descrever o pedido principal — pagamento, obrigação de fazer/não fazer, etc.] no valor de R$ [valor] (por extenso), acrescido de correção monetária pelo [índice] e juros de [taxa] ao mês desde [data];

c) [PREENCHER: pedidos adicionais, se houver — tutela de urgência, produção de provas, etc.]

d) A condenação do(a) Réu(é) ao pagamento das custas processuais e honorários advocatícios, nos termos do art. 85 do CPC/2015;

e) A produção de todos os meios de prova em direito admitidos, especialmente documental, testemunhal e pericial, que se fizerem necessários.

**IV — DO VALOR DA CAUSA**

Atribui-se à presente causa o valor de R$ [PREENCHER: valor] ([por extenso]), nos termos do art. 292 do CPC/2015.

Nesses termos,
Pede deferimento.

[LOCAL], [DATA]

_______________________________________________
**[NOME COMPLETO DO ADVOGADO]**
OAB/[UF] nº [número]
[endereço profissional] — [telefone] — [e-mail]

---
*⚠️ Rascunho de demonstração — AI_PROVIDER=mock. Configure ANTHROPIC_API_KEY e AI_PROVIDER=anthropic para geração real com base nas instruções fornecidas.*
MOCK,

        'chat' => <<<'MOCK'
Olá! Sou o assistente jurídico JusAI em **modo de demonstração**.

Estou pronto para ajudá-lo(a) com análises jurídicas, pesquisa de legislação, revisão de documentos e elaboração de minutas relacionadas a este caso.

No momento, a IA está operando em modo mock (`AI_PROVIDER=mock`). Para respostas reais baseadas no conteúdo do caso, configure sua `ANTHROPIC_API_KEY` no arquivo `.env` e defina `AI_PROVIDER=anthropic`.

Como posso ajudá-lo(a) com as questões jurídicas deste caso?
MOCK,

    ],

];
