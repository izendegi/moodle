# Custom Git-Style Quotes Filter for Moodle

This plugin extends the default Markdown filter in Moodle by adding support for git-style quotes, such as `[!NOTE]`, `[!TIP]`, `[!IMPORTANT]`, and `[!WARNING]`. It also applies custom styles to standard blockquotes, providing better visual distinction between different types of quotes.

## Features

- **Git-style quotes:** Adds custom styles and icons to `[!NOTE]`, `[!TIP]`, `[!IMPORTANT]`, and `[!WARNING]` quotes in Markdown.
- **Standard blockquote styling:** Automatically applies a custom style to standard blockquotes without special tags.
- **Customizable design:** You can easily modify the CSS to adjust the appearance of the quotes to fit your site's theme.

## Installation

1. **Download and Install:**
   - Clone or download this repository to your Moodle `/filter` directory.
   - The folder should be named `gitquotes`.
   - Navigate to `Site Administration > Plugins > Install plugins` in your Moodle dashboard.

2. **Activate the Filter:**
   - Go to `Site Administration > Plugins > Filters > Manage filters`.
   - Enable the "Git-style Quotes" filter.
   - Make sure this filter is placed above the default Markdown filter to ensure proper functionality.

3. **Clear Moodle Cache:**
   - After installation, go to `Site Administration > Development > Purge caches` and click **Purge all caches** to refresh your Moodle filters.

## Usage

Once the plugin is installed and activated, you can use the following syntax in your Markdown content:

- **Note Example:**
  ```markdown
  > [!NOTE]
  > This is a note example.
  ```

- **Tip Example:**
  ```markdown
  > [!TIP]
  > This is a tip example.
  ```

- **Important Example:**
  ```markdown
  > [!IMPORTANT]
  > This is an important example.
  ```

- **Warning Example:**
  ```markdown
  > [!WARNING]
  > This is a warning example.
  ```

- **Standard Blockquote Example:**
  ```markdown
  > This is a standard blockquote without special tags.
  ```

All blockquotes will be styled according to the type of quote used, and standard blockquotes will have a default style applied.

## Customization

You can easily customize the appearance of the quotes by editing the `gitquotes` CSS class in the plugin's stylesheet. For example, you can change the colors for different types of quotes (`NOTE`, `TIP`, `IMPORTANT`, `WARNING`) by modifying the CSS in the `styles.css` file.

### Example CSS (in `styles.css`):

```css
.gitquote.NOTE {
    border-color: #0366d6;
    background-color: #e7f0ff;
}

.gitquote.WARNING {
    border-color: #d73a49;
    background-color: #fce8e6;
}

.gitquote.TIP {
    border-color: #28a745;
    background-color: #e6f4ea;
}

.gitquote.IMPORTANT {
    border-color: #800080;
    background-color: #f3e5f5;
}
```

## License

This plugin is licensed under the [GNU General Public License v3](https://www.gnu.org/licenses/gpl-3.0.html).

## Support

For any issues or suggestions, please submit an issue in this repository or reach out via the Moodle plugin directory.