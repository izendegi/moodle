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
 * local_slow_queries.php
 *
 * @package   local_slow_queries
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['col_avgtime'] = 'Tempo médio (s)';
$string['col_count'] = 'Quantidade';
$string['col_cron'] = 'CRON';
$string['col_origin'] = 'Backtrace';
$string['col_parameters'] = 'Parâmetros';
$string['col_sqlpreview'] = 'SQL';
$string['comments_title'] = 'Comentários';
$string['detail_indexes'] = 'Possíveis índices ausentes';
$string['detail_indexes_none'] = 'Nenhuma sugestão de índice foi detectada para esta consulta.';
$string['detail_indexes_notice'] = 'As sugestões são heurísticas. Teste com cuidado em um ambiente de homologação e valide com EXPLAIN/ANALYZE.';
$string['detail_sql'] = 'SQL e parâmetros';
$string['detail_sql_expanded'] = 'SQL com parâmetros';
$string['detail_sql_expanded_desc'] = 'Expansão em melhor esforço para análise (apenas exibição)';
$string['detail_title'] = 'Detalhes da consulta';
$string['duration_days'] = '{$a} dia(s)';
$string['duration_hours'] = '{$a} hora(s)';
$string['duration_minutes'] = '{$a} minuto(s)';
$string['duration_seconds'] = '{$a} segundo(s)';
$string['filter_apply'] = 'Aplicar';
$string['filter_minexec'] = 'Tempo mín. de execução (s)';
$string['filter_search'] = 'Buscar SQL';
$string['filter_search_ph'] = 'Digite parte do SQL para buscar...';
$string['filter_title'] = 'Filtros';
$string['index_title'] = 'Consultas lentas';
$string['logslow_warning_body'] = 'Esta página lê de <code>mdl_log_queries</code>, mas seu site não está configurado para registrar consultas SQL lentas. Ative <code>logslow</code> em <code>config.php</code> (defina como <code>true</code> ou como um número em segundos). Exemplo:';
$string['logslow_warning_current'] = 'Valor atual';
$string['logslow_warning_hint'] = 'Depois de salvar o <code>config.php</code>, reproduza a página/tarefa de cron lenta e então atualize esta página para ver novas entradas.';
$string['logslow_warning_title'] = 'O registro de consultas lentas está desativado';
$string['nav_index'] = 'Consultas lentas';
$string['pluginname'] = 'Consultas lentas';
$string['privacy:metadata'] = 'O plugin Consultas lentas não armazena nenhum dado pessoal. Ele apenas exibe aos administradores registros existentes do log de consultas do banco de dados.';
$string['timeline_calendar'] = 'Linha do tempo no calendário';
$string['timeline_last_7'] = '<strong>{$a}</strong> execuções nos últimos 7 dias';
$string['timeline_scale'] = 'A escala corresponde a 1 segundo a cada {$a} pixels';
$string['timeline_title'] = 'Linha do tempo das consultas';
$string['timeline_totaltime'] = 'No total, o banco de dados gastou <strong>{$a}</strong> executando estas instruções SQL';
