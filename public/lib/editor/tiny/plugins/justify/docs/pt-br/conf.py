import moodle_docs_theme

project = "moodle-tiny_justify"
copyright = "2026, CTE-ZL IFRN & Kelson da Costa Medeiros"
author = "Kelson da Costa Medeiros"
release = "1.0.22"

extensions = [
    "sphinx.ext.githubpages",
    "moodle_docs_theme",
]

templates_path = []
exclude_patterns = ["_build", "Thumbs.db", ".DS_Store"]

language = "pt_BR"

html_theme = "moodle_docs_theme"
html_theme_path = [moodle_docs_theme.get_html_theme_path()]

html_theme_options = {
    "project_name": "moodle-tiny_justify",
    "tagline": "Botão de alinhamento justificado para o editor TinyMCE no Moodle",
    "github_url": "https://github.com/moodle-by-kelsoncm/moodle-tiny_justify",
    "github_repo": "moodle-by-kelsoncm/moodle-tiny_justify",
    "github_version": "main",
    "doc_path": "docs/pt-br/",
    "show_edit_on_github": True,
    "enable_dark_mode": True,
    "enable_language_selector": True,
    "navigation_links": "Início|index, Instalação|installation, Configuração|configuration, Uso|usage",
}

html_static_path = []
