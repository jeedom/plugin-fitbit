Plugin para acessar informações em dispositivos Fitbit
(número de etapas, calorias, distância ...)

Configuração 
=============

Depois que o plug-in Fitbit estiver instalado, vá para :

![fitbit 1](../images/fitbit-1.JPG)

Criação e associação de equipamentos com a conta Fitbit 
--------------------------------------------------------

> **Tip**
>
> Como em muitos lugares em Jeedom, coloque o mouse na extremidade esquerda
> abre um menu de acesso rápido (você pode
> dos seus perfis sempre o deixe visível)

Clique em adicionar uma pessoa para adicionar alguém :

![fitbit 2](../images/fitbit-2.JPG)

Dê um nome a esta pessoa (este equipamento) e valide :

![fitbit 3](../images/fitbit-3.JPG)

Você deve ter esta página :

![fitbit 4](../images/fitbit-4.JPG)

> **Important**
>
> A primeira coisa a verificar é "URL de retorno". Isso deve ser
> validado e acessível a partir do exterior, caso contrário você não poderá
> vincule o Jeedom à sua conta Fitbit. Caso contrário, coloque
> atualize as configurações da rede em Geral →
> Administração → Configuração e parte "redes", consulte
> [aqui](https://www.jeedom.fr/doc/documentation/core/fr_FR/doc-core-administration.html.html#administration)

> **Note**
>
> Recomenda-se ao conectar entre Jeedom e Fitbit a ser conectado
> para Jeedom pelo ip externo

Para vincular sua conta Fitbit ao Jeedom, crie primeiro
uma conta de desenvolvedor Fitbit indo
[aqui](https://dev.fitbit.com/fr), então você precisa fazer login
então faça : "Registrar um aplicativo". Em seguida, preencha as informações
:

-   Nome da aplicação : Jeedom

-   Descrição : Jeedom

-   Application Website : <https://jeedom.com>

-   Organização : jeedom

-   Organização Website : <https://jeedom.com>

-   OAuth 2.0 Tipo de Aplicação : Server

-   URL de retorno de chamada : Você tem que colocar o que Jeedom lhe dá durante o
    criação de equipamento Fitbit, tenha cuidado se não
    bom a conexão não pode ser feita

-   Tipo de acesso padrão : Ler e escrever

Você também pode adicionar uma assinatura, isso permite que o Fitbit
envie uma atualização diretamente para o Jeedom (em vez de aguardar
Informações de atualização do Jeedom) :

-   Padrão : oui

-   URL do terminal : o dado por jeedom

-   Tipo : Corpo JSON

-   ID do Assinante : não ponha nada

Se você possui uma assinatura, deve (tenha cuidado para fazer tudo
estas etapas em ordem) :

-   Coloque o número da assinatura no equipamento Fitbit na Jeedom

-   Coloque o código de assinatura no equipamento Fitbit no Jeedom

-   Equipamento de backup

-   No site Fitbit, inicie a verificação da assinatura

-   Em seguida, "Vincule a um usuário" o equipamento jeedom, como abaixo

> **Important**
>
> No momento, o Fitbit suporta https muito mal; portanto, se você estiver em https
> há pouca chance de que funcione

Clique em "Vincular ao usuário" para vincular este dispositivo ao seu
Conta Fitbit :

![fitbit 5](../images/fitbit-5.JPG)

Insira as credenciais da sua conta Fitbit e valide o
pedido de autorização :

Se você receber uma página em branco ou um erro, seu
configuração de rede não é boa; caso contrário, você deve voltar a
esta página :

![fitbit 6](../images/fitbit-6.JPG)

Para a assinatura, você precisa

Configuração 
-------------

Aqui estão os detalhes da configuração do plugin :

![fitbit 6](../images/fitbit-6.JPG)

-   Nome da pessoa : nome do equipamento Fitbit

-   Objeto pai : nome do objeto ao qual conectar o equipamento

-   Ativar / Visível : ativa o equipamento (não se esqueça de
    faça isso caso contrário você não terá dados) e o torna visível
    no painel

-   Para cada pedido :

    -   Historicizar : permite registrar o comando

    -   Display : permite torná-lo visível ou não no painel

    -   Avançado (pequenas rodas dentadas) : exibe o
        configuração de controle avançado

    -   Teste : permite testar o comando para ver seu valor

    -   Excluir (botão "-") : para excluir o pedido

Widget 
------

Aqui está o widget fitbit :

![fitbit 7](../images/fitbit-7.JPG)
