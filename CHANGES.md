# Changes — environment-variable

## 2.1 Remove leaked value from exception message

**Arquivo(s):** `src/Internal/Exceptions/InvalidEnvironmentValue.php`

**O que mudou:** O template da mensagem não inclui mais o conteúdo do valor da variável. O parâmetro `$value` das factories estáticas foi mantido na assinatura (compatibilidade), mas passou a ser descartado. A property `private readonly string $value` foi removida.

**Por quê:** Valores de variáveis de ambiente frequentemente contêm segredos (tokens, senhas). Incluí-los na mensagem da exceção permitiria vazamento em logs de erro.

---

## 2.2 Use FILTER_VALIDATE_INT for integer conversion

**Arquivo(s):** `src/EnvironmentVariable.php`

**O que mudou:** `toInteger()` agora usa `filter_var(..., FILTER_VALIDATE_INT)` em vez de `is_numeric`.

**Por quê:** `is_numeric` aceita strings como `'99.99'` e `'1e2'`, produzindo truncamento silencioso ao aplicar `(int)`. `FILTER_VALIDATE_INT` rejeita não-inteiros explicitamente. Corrigida correspondente entrada de teste para que `'99.99'` não seja mais considerada válida.

---

## 2.3 Lookup consults `$_ENV`, `$_SERVER`, `getenv()` in order

**Arquivo(s):** `src/EnvironmentVariable.php`

**O que mudou:** Adicionado helper privado estático `lookup()` que consulta `$_ENV`, `$_SERVER` e depois `getenv()`. `from()` e `fromOrDefault()` usam esse helper.

**Por quê:** Ambientes reais populam variáveis em lugares diferentes (`$_ENV` via `variables_order`, `$_SERVER` via web servers, `getenv()` via `putenv`/ambiente do processo). Consultar apenas `getenv()` pode perder valores definidos em outros mecanismos.
