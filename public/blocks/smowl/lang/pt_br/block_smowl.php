<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Lang File.
 *
 * @package     block_smowl
 * @author      Smowltech <info@smowltech.com>
 * @copyright   Smiley Owl Tech S.L.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'SMOWL';

// P&P manage.

$string['typeconfigheading'] = 'Tipo de configuração';
$string['typeconfigheadingdesc'] = 'Selecione o tipo de configuração <br> · Automático <br> · Manual <br> · Configurar mais tarde';
$string['typeconfigheadingdesc2'] = 'Vocéa pode alterar seu tipo de configuração';

$string['typeconfig'] = 'Tipo de configuração';
$string['typeconfigdesc'] = 'Selecione a ação que deseja executar abaixo';
$string['noconfigmessage'] = 'Vocéa selecionou configurar SMOWL mais tarde, vocéa pode fazer isso na administração do campus.';

$string['changetypeconfigauto'] = 'Mude para configuração automática.';
$string['changetypeconfigrestartauto'] = 'Reinicie a configuração automática.';
$string['changetypeconfigmanual'] = 'Mude para configuração manual.';
$string['changetypeconfigcancel'] = 'Cancelar alteração de configuração.';
$string['changetypeconfigafter'] = 'Configure mais tarde.';

$string['typeask'] = 'Selecione uma opção';
$string['typenoconfig'] = 'Configure mais tarde';
$string['typeautoconfig'] = 'Configuração automática';
$string['typemanualconfig'] = 'Configuração manual';

$string['dataautoconfigheading'] = 'Dados de configuração automática';
$string['dataautoconfigheadingdesc'] = 'Agora vocéa pode validar ou inserir os dados de configuração automática';

$string['autoconfigmessagejsondataclient'] = 'As informações do cliente são pré9-configuradas neste plugin.';
$string['autoconfigmessageconfigdone'] = 'O plugin SMOWL foi configurado automaticamente';

$string['autoconfigmessageclientvoid'] = 'As informações do cliente devem ser configuradas corretamente';
$string['autoconfigmessagecontenterror'] = 'Falha na configuração automática: retorno com itens inválidos';
$string['autoconfigmessageunauthorized'] = 'Falha na configuração automática: problemas de autenticação';
$string['autoconfigmessagebadrequest'] = 'Falha na configuração automática: solicitação inválida';
$string['autoconfigmessageentityerror'] = 'Falha na configuração automática: não é9 possédvel obter a entidade';
$string['autoconfigmessageconflict'] = 'Falha na configuração automática: problemas internos na entidade';

$string['clientid'] = 'ID do cliente';
$string['clientiddesc'] = 'ID do cliente para ativar o campus';

$string['clientkey'] = 'Chave de ativação';
$string['clientkeydesc'] = 'Chave de ativação para ativar o campus';

$string['nocapabilityconfigmessage'] = 'Vocéa não tem permissão para executar esta ação';

// Connection smowl settings.
$string['connectionsconfig'] = 'Configurações de conexão';
$string['connectionsconfigdesc'] = 'As seguintes opções permitem que você se conecte ao SMOWL';
$string['connectionsconfigcontact'] = 'Para obter mais informações sobre sua licença atual, acesse sua conta de cliente em <a href="https://my.smowltech.net" target="_blank">https://my.smowltech.net</a>.';

$string['entity'] = 'Plataforma';
$string['entitydesc'] = 'ID fornecido pela Smowltech. ';
$string['noticeemptyentitysettings'] = 'A plataforma está vazia, entre em contato com a Smowltech para saber o valor a ser inserido. ';

$string['password'] = 'Chave de licença';
$string['passworddesc'] = 'Chave de licença para a plataforma, fornecida pela Smowltech.';
$string['noticeemptypasswordsettings'] = 'A chave de licença está vazia, entre em contato com a Smowltech para saber o valor a ser inserido.';

$string['apikey'] = 'Chave da API';
$string['apikeydesc'] = 'Chave para acessar a API, fornecida pela Smowltech.';
$string['noticeemptyapikeysettings'] = 'A chave da API está vazia, entre em contato com a Smowltech para saber o valor a ser inserido.';

// JWT Secret
$string['smowljwtsecret'] = 'Chave secreta JWT';
$string['smowljwtsecretdesc'] = 'Chave secreta JWT para acessar a API, fornecida pela Smowltech.';
$string['noticeemptysmowljwtsecret'] = 'A chave secreta JWT está vazia, entre em contato com a Smowltech para saber o valor a ser inserido.';

// Instructors smowl settings.
$string['instructorsconfig'] = 'Configurações para instrutores';
$string['instructorsconfigdesc'] = 'As seguintes opções personalizam o uso do SMOWL para instrutores nos cursos em que o bloco está ativo.';

$string['continuousassessment'] = 'Avaliação contínua';
$string['onlyexams'] = 'Provas\' Monitoramento';

$string['tracking'] = 'Tipo de monitoramento';
$string['trackingdesc'] = 'Selecione se o tipo de rastreamento SMOWL é avaliação contínua ou apenas exames.';

$string['attempttracking'] = 'Distinção das tentativas de exame';
$string['attempttrackingdesc'] = 'Selecione se você deseja diferenciar entre diferentes tentativas do mesmo exame para um aluno.';

// Settings advancer instructors.
$string['viewsettingsadvancedinstructorstit'] = 'Configurações avançadas para instrutores';
$string['viewsettingsadvancedinstructors'] = 'Ver configurações avançadas para instrutores';
$string['viewsettingsadvancedinstructorsdesc'] = 'Se esta opção estiver ativada, as opções avançadas para instrutores serão exibidas.';

$string['accesscontrol'] = 'Controle de acesso';
$string['accesscontroldesc'] = 'Permitir que os usuários acessem o exame apenas se as ferramentas SMOWL estiverem ativas';

// Settings advancer users.
$string['usersconfig'] = 'Configurações para usuários';
$string['usersconfigdesc'] = 'As seguintes opções personalizam o uso do SMOWL para usuários nas atividades e cursos em que o sistema de monitoramento está ativo.';

$string['floatblock'] = 'Ativar bloco de supervisão flutuante';
$string['floatblockdesc'] = 'Selecione para ver o bloco flutuante';
$string['activeinpopup'] = 'A supervisão está disponível no bloco flutuante';

$string['blockheight'] = 'Tamanho da tela';
$string['blockheightdesc'] = 'Altura do iframe onde a webcam será exibida durante o monitoramento.';
$string['noticeblockheight'] = 'A altura do iframe não pode ser inferior a 280 px.';

// Settings advancer users.
$string['viewsettingsadvanceduserstit'] = 'Configurações avançadas para usuários';
$string['viewsettingsadvancedusers'] = 'Ver configurações avançadas para usuários';
$string['viewsettingsadvancedusersdesc'] = 'Se esta opção estiver ativada, as opções avançadas para usuários serão exibidas.';

// Capabilities.
$string['smowl:addinstance'] = 'Adicionar bloco SMOWL';
$string['smowl:manageactivities'] = 'Gerenciar atividades SMOWL';
$string['smowl:managegroups'] = 'Gerenciar grupos SMOWL';
$string['smowl:viewstudentcontent'] = 'Ver links de estudantes';
$string['smowl:enrolment'] = 'Acesso a inscrição SMOWL';

// Notices.
$string['notinstancedblockincourse'] = 'Para executar esta ação, você deve criar o bloco SMOWL no curso.';
$string['notmanagepermissions'] = 'Você não tem permissão para gerenciar atividades SMOWL.';
$string['notteachersmanagementpermissions'] = 'Os professores não têm permissão para gerenciar atividades SMOWL.';
$string['notviewmanagepermissions'] = 'Você não tem permissão para visualizar ou gerenciar atividades SMOWL.';
$string['cannotcreatefile'] = 'O arquivo não pôde ser criado';
$string['activitiesupdate'] = 'Atividades SMOWL atualizadas';
$string['activityupdate'] = 'Atividade SMOWL atualizada';
$string['noticeemptysmowlconfig'] = 'A configuração do bloco nao foi concluída. <br/>'.
    'Contate o administrador da plataforma para resolver o problema';
$string['noticeemptyentitynavigation'] = 'A configuração do bloco SMOWL não foi concluída. <br/>'.
    'Contate o administrador da plataforma para resolver o problema.';

// Privacy.
$string['privacy:metadata'] = 'O sistema SMOWL exibe apenas os dados armazenados nos servidores Smowltech.';
$string['privacy:metadata:smowl:smowltech_net'] = 'O sistema SMOWL apenas exibe os dados armazenados e transmite os dados do usuário do Moodle para os servidores da Smowltech.';
$string['privacy:metadata:smowl:smowltech_net:user_id'] = 'Identificador único do usuário';

// Events.
$string['eventinstancecreated'] = 'Evento criado instância de bloco';
$string['createblockinstance'] = 'Bloco criado na instância ';
$string['eventinstancedeleted'] = 'Evento excluído no bloco da instância';
$string['deleteblockinstance'] = 'Deletada instância de bloco: ';
$string['eventinstanceupdated'] = 'Instância de bloco atualizado de evento';
$string['updateblockinstance'] = 'Instância de bloco atualizada ';
$string['fromoldblockname'] = ' do antigo bloco ';
$string['apicalled'] = 'Chamada API SMOWL feita';
$string['apicalleddesc'] = 'Chamada API SMOWL de paré2metros feita.';

// Internal URL SMOWL Params.
$string['internalconfig'] = 'Configurações internas do SMOWL';
$string['internalconfigdesc'] = 'As seguintes opções podem afetar a operação do plugin,'.
    ' eles devem ser modificados somente a pedido expresso do SMOWL ';
$string['onlysmowlexpressrequest'] = 'Esta opção deve ser modificada somente a pedido expresso do SMOWL.';
$string['viewsettingsinternal'] = 'Ver opções internas';
$string['viewsettingsinternaldesc'] = 'Ative esta verificação para ver as opções internas do SMOWL.';

$string['internalconfigurls'] = 'URLs internas do SMOWL';
$string['urlstudentview'] = 'Link de visualização do aluno';
$string['urlstudentviewdesc'] = 'Link para visualização do aluno.';

// API URLs.
$string['internalconfigapiurls'] = 'URLs internas da API SMOWL';
$string['urlsmowlapi'] = 'URL da API SMOWL';
$string['urlsmowlapidesc'] = 'URL para acessar a API SMOWL.';

$string['apilmssettings'] = 'URL de configuração do LMS';
$string['apilmssettingsdesc'] = 'URL para atualizar as configurações do LMS nas instalações automáticas.';

$string['apilmssettingscustomer'] = 'URL de configuração do LMS do cliente';
$string['apilmssettingscustomerdesc'] = 'URL para atualizar as configurações do LMS do cliente nas instalações automáticas.';

$string['apiconfigclient'] = 'URL de configuração do cliente';
$string['apiconfigclientdesc'] = 'URL para ativar a integração nas instalações automáticas.';

$string['apiaddactivity'] = 'Adicionar atividade';
$string['apiaddactivitydesc'] = 'URL para adicionar atividade supervisionada';

$string['apimodifyactivity'] = 'Modificar atividade';
$string['apimodifyactivitydesc'] = 'URL para modificar a atividade supervisionada';

// Accessrule smowlcheckcam Settings.
$string['accesrulesmowlcheckcamconfig'] = ' URL de ativação da verificação da webcam';
$string['accesrulesmowlcheckcamconfigdesc'] = 'URL para ativar a verificação da webcam nas instalações automáticas.';
$string['accesrulesmowlcheckcam'] = 'URL de verificação da webcam';
$string['accesrulesmowlcheckcamdesc'] = 'URL para acessar a verificação da webcam nas instalações automáticas.';

// View smowl settings.
$string['viewconfig'] = 'Visualizar configurações';
$string['viewconfigdesc'] = 'As seguintes opções afetam a exibição do bloco ';
$string['floatsnap'] = 'Ativar bloco flutuante no tema Snap';
$string['floatsnapdesc'] = 'Selecione para visualizar o bloco flutuante, ele só funciona para o tema SNAP';

// Manage SMOWL Groups.
$string['managegroups'] = 'Grupos\' Gerenciamento';
$string['groupsaccessrestrictions'] = 'Restrições de acesso';

$string['managegroupsformintro'] = 'No formulário a seguir, você deve selecionar os grupos de usuários ou agrupamentos para os quais o bloco SMOWL será exibido.';
$string['managegroupsupdate'] = 'Grupos com SMOWL ativo atualizados corretamente.';

$string['availabilityconditionsjsonform'] = 'Restrições de acesso';
$string['availabilityconditionsjsonform_help'] = 'Nesse menu, você pode adicionar as restrições de acesso necessárias.';

// Setting Manage Groups.
$string['notmanagegroupspermissions'] = 'Você não tem permissão para gerenciar grupos.';
$string['managegroupsnotconfigured'] = 'O gerenciamento de grupos não está configurado.';

// Bulk actions.

$string['bulkactions'] = 'Ações em massa';
$string['bulkactionsdesc'] = 'As seguintes opções permitem que vocéa ative ou desative SMOWL em massa.';
$string['noticeactivebulkactions'] = 'Ativar ou desativar SMOWL em massa pode levar algum tempo, dependendo do número de atividades ou grupos selecionados.';
$string['bulkactive'] = 'Ativação de atividades';
$string['bulkgroups'] = 'Ativação de grupos';
$string['coursecategory'] = 'Course category';
$string['coursecategory_help'] = 'Categoria do curso para SMOWL ativo.';
$string['activitytype'] = 'Tipo de Atividade';
$string['activitytype_help'] = 'Tipo de atividade para SMOWL ativo.';
$string['activityname'] = 'Nome da atividade';
$string['activityname_help'] = 'Nome da atividade para SMOWL ativo.';
$string['searchactivities'] = 'Procurar Atividades';
$string['allcategories'] = 'Todas as categorias';
$string['searchresults'] = 'Procurar resultados';
$string['notfound'] = 'Resultados não encontrados';
$string['savechanges'] = 'Salvar mudanças';
$string['bulkactiveupdate'] = 'Atividades em massa SMOWL\' configurações atualizadas com sucesso';
$string['searchgroups'] = 'Procurar Grupos';
$string['groupname'] = 'Nome do grupo';
$string['groupname_help'] = 'Nome do grupo para SMOWL ativo';
$string['managebulkgroupsformintro'] = 'No seguinte formulário você pode ver todos os grupos de cursos resultantes da pesquisa.';
$string['managebulkgroupsforminfo'] = 'Os usuários pertencentes a qualquer um dos grupos selecionados poderão acessar a visualização.';
$string['managebulkgroupsformmoreinfo'] = 'É importante que todos os usuários do curso pertençam a pelo menos um grupo.';
$string['notviewmanagebuklpermissions'] = 'Você não tem permissão para visualizar ou gerenciar atividades SMOWL em massa.';

// Access Control Status.
$string['acwaiting'] = 'Verificação SMOWL, aguarde';
$string['acaccess'] = 'SMOWL ativado, vocéa pode acessar sua atividade';
$string['acnotaccess'] = 'SMOWL não foi validado, recarregue o navegador para verificar novamente';

// LTI Integration.

$string['internalconfigltitool'] = 'URL de configuração LTI SMOWL';
$string['ltitoolname'] = 'SMOWL LTI';
$string['urlsmowlltitool'] = 'URL LTI base';
$string['urlsmowlltitooldesc'] = 'URL base da ferramenta LTI SMOWL';
$string['ltitoolinit'] = 'URL inicial';
$string['ltitoolinitdesc'] = 'URL inicial da ferramenta LTI SMOWL';
$string['ltitoolversion'] = 'Versão LTI';
$string['ltitoolversiondesc'] = 'Versão da ferramenta LTI SMOWL';
$string['ltitoolpublickeyset'] = 'URL keyset';
$string['ltitoolpublickeysetdesc'] = 'URL do keyset público da ferramenta LTI SMOWL';
$string['ltitoolinitiatelogin'] = 'URL de login inicial';
$string['ltitoolinitiatelogindesc'] = 'URL de login inicial da ferramenta LTI SMOWL';
$string['ltitoolredirection'] = 'URI de redirecionamento';
$string['ltitoolredirectiondesc'] = 'URI de redirecionamento da ferramenta LTI SMOWL';
$string['ltitoolconfigusage'] = 'Uso da configuração';
$string['ltitoolconfigusagedesc'] = 'Uso da configuração da ferramenta LTI SMOWL como \"Mostrar como ferramenta pré9-configurada ao adicionar uma atividade externa\"';
$string['ltitoollaunch'] = 'Contéainer de lançamento padrão';
$string['ltitoollaunchdesc'] = 'Contéainer de lançamento padrão da ferramenta LTI SMOWL como \"Incorporar, sem blocos\"';
$string['ltitoolconfigmemberships'] = 'Membros padrão';
$string['ltitoolconfigmembershipsdesc'] = 'Membros padrão da ferramenta LTI SMOWL como \"Incorporar, sem blocos\"';

$string['urlsmowlltiapi'] = 'URL base da API LTI';
$string['urlsmowlltiapidesc'] = 'URL base da API LTI SMOWL';
$string['ltiapiapplications'] = 'Aplicações LTI';
$string['ltiapiapplicationsdesc'] = 'Aplicações LTI da API LTI SMOWL';
$string['ltiapideployments'] = 'Deployments LTI';
$string['ltiapideploymentsdesc'] = 'Deployments LTI da API LTI SMOWL';

// LTI problems.
$string['lticreatetoolsuccess'] = 'Ferramenta LTI SMOWL criada com sucesso';
$string['ltiupdatetoolsuccess'] = 'Ferramenta LTI SMOWL atualizada com sucesso';
$string['lticreatetoolerror'] = 'Problemas ao criar a ferramenta LTI SMOWL';
$string['ltisendtoolerror'] = 'Problemas ao enviar a configuração LTI para SMOWL';
$string['ltisendtoolneedactivation'] = 'Atenção! Parece que sua entidade ainda não foi validada no aplicativo mySmowltech, o que impede a conclusão da configuração do plugin. <a href="https://my.smowltech.net/" target="_blank">Clique aqui</a> e faça login com suas credenciais de login mySmowltech para validar sua entidade. Esta validação é necessária para concluir a integração com êxito.';
$string['lticreatewserror'] = 'Problemas ao criar o WS SMOWL';
$string['ltiactivewserror'] = 'Problemas ao ativar o WS SMOWL';
$string['lticreateusererror'] = 'Existem problemas ao criar o usuário "Smowl Webservices User". É necessária uma ação do administrador para ativá-lo.';
$string['noticeltinotvisible'] = 'As ferramentas LTI estão bloqueadas na administração do campus (Plugins / Gerenciar atividades).';
$string['ltisendwserror'] = 'Problemas ao enviar as informações WS';
$string['ltiltiactivityerror'] = 'Problemas ao criar a atividade LTI SMOWL';
$string['notlticourse'] = 'Do not have LTI in course.';

// LTI internal config.
$string['internalconfiglticonfig'] = 'Configurações internas LTI SMOWL';
$string['ltientity'] = 'Entidade LTI';
$string['ltitypeid'] = 'Tipo de ID LTI';
$string['ltideploymentid'] = 'Deployment LTI';
$string['ltiappid'] = 'Aplicação LTI';
$string['ltirestid'] = 'REST LTI';

$string['ltiactivityname'] = 'Painel SMOWL';

// Block links teacher.
$string['ltimanagesmowl'] = 'Painel de monitoramento';
$string['teachercontent'] = 'Configure o rastreamento e analise os resultados.';
$string['teacherbutton'] = 'Acesse o SMOWL';

// Block links Student.
$string['ltistudentsmowl'] = 'Painel SMOWL';
$string['studentcontent'] = 'Acesse o painel de cadastro e download do SMOWL';
$string['studentbutton'] = 'Acesse o SMOWL';

$string['notstudentaccesspermissions'] = 'Somente estudantes podem acessar esta seção';

// Corner
$string['drag_me'] = 'Arraste-me';