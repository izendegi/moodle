Guia de Uso & Desenvolvimento
=============================

Utilização no Editor
--------------------

1. Abra qualquer área de texto que utilize o editor TinyMCE no Moodle.
2. Selecione o texto ou parágrafo que deseja justificar.
3. Clique no botão **Justify** na barra de ferramentas.

Permissão
---------

O botão aparece para quem tem a permissão ``tiny/justify:use``, concedida a todos os papéis por padrão. Proíba-a num papel ou contexto para esconder o botão ali.

Desenvolvimento & Compilação AMD
--------------------------------

Ao modificar arquivos dentro de `amd/src/`:

1. Atualize a versão em `version.php`.
2. Compile os módulos AMD com Grunt na raiz do Moodle:

   .. code-block:: bash

      npx grunt amd --root=lib/editor/tiny/plugins/justify

3. Limpe os caches via CLI:

   .. code-block:: bash

      php admin/cli/purge_caches.php
