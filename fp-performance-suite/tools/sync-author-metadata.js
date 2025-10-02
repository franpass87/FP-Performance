#!/usr/bin/env node
const fs = require('fs');
const path = require('path');

const AUTHOR = {
  name: 'Francesco Passeri',
  email: 'info@francescopasseri.com',
  uri: 'https://francescopasseri.com',
  homepage: 'https://francescopasseri.com',
};

const applyFromEnv = (() => {
  const value = process.env.APPLY;
  return typeof value === 'string' && ['1', 'true', 'yes'].includes(value.toLowerCase());
})();

const argv = process.argv.slice(2);
const applyFromArgs = argv.some((arg) => {
  if (arg === '--apply') {
    return true;
  }
  if (arg.startsWith('--apply=')) {
    const [, value] = arg.split('=');
    return ['1', 'true', 'yes'].includes((value || '').toLowerCase());
  }
  return false;
});

const apply = applyFromEnv || applyFromArgs;
const docsOnly = argv.includes('--docs');

const root = path.join(__dirname, '..');
const report = [];

function updateFile(relativePath, updater) {
  const filePath = path.join(root, relativePath);
  if (!fs.existsSync(filePath)) {
    return;
  }

  const original = fs.readFileSync(filePath, 'utf8');
  const { content, fields } = updater(original) || {};

  if (!content || content === original) {
    return;
  }

  if (apply) {
    fs.writeFileSync(filePath, content, 'utf8');
  } else {
    const backupPath = `${filePath}.bak`;
    fs.writeFileSync(backupPath, content, 'utf8');
  }

  report.push({ file: relativePath, fields });
}

function normaliseReadmeMd(content) {
  let updated = content;
  const replacements = [
    {
      regex: /(\| \*\*Author\*\* \| ).*?\|/,
      value: `$1[${AUTHOR.name}](${AUTHOR.uri}) |`,
      field: 'README.md author',
    },
    {
      regex: /(\| \*\*Author Email\*\* \| ).*?\|/,
      value: `$1[${AUTHOR.email}](mailto:${AUTHOR.email}) |`,
      field: 'README.md author email',
    },
    {
      regex: /(\| \*\*Author URI\*\* \| ).*?\|/,
      value: `$1${AUTHOR.uri} |`,
      field: 'README.md author uri',
    },
    {
      regex: /(\| \*\*Plugin Homepage\*\* \| ).*?\|/,
      value: `$1${AUTHOR.homepage} |`,
      field: 'README.md homepage',
    },
  ];

  const fields = [];
  replacements.forEach(({ regex, value, field }) => {
    if (regex.test(updated)) {
      updated = updated.replace(regex, value);
      fields.push(field);
    }
  });

  const description = 'Modular performance suite for shared hosting with caching, asset tuning, WebP conversion, database cleanup, and safe debug tools.';
  updated = updated.replace(/^> .*\n\n/m, `> ${description}\n\n`);

  return { content: updated, fields };
}

function normaliseReadmeTxt(content) {
  let updated = content;
  const replacements = [
    {
      regex: /(Contributors:\s*)(.*)/,
      value: '$1francescopasseri, franpass87',
      field: 'readme.txt contributors',
    },
    {
      regex: /(Plugin Homepage:\s*).*/,
      value: `$1${AUTHOR.homepage}`,
      field: 'readme.txt homepage',
    },
  ];

  const fields = [];
  replacements.forEach(({ regex, value, field }) => {
    if (regex.test(updated)) {
      updated = updated.replace(regex, value);
      fields.push(field);
    }
  });

  const description = 'Modular performance suite for shared hosting with caching, asset tuning, WebP conversion, database cleanup, and safe debug tools.';
  updated = updated.replace(/\n\n== Description ==\n\n[\s\S]*?\n\n= Features =/, `\n\n== Description ==\n\nFP Performance Suite delivers a modular control center for WordPress administrators on shared hosting. It combines page caching, browser cache headers, asset optimization, WebP conversion, database cleanup, debug toggles, realtime log viewing, and hosting-specific presets behind a unified dashboard with safety guards.\n\n= Features =`);
  updated = updated.replace(/\n\nModular performance suite[^\n]*\n/, `\n\n${description}\n`);

  return { content: updated, fields };
}

function normaliseComposer(content) {
  const data = JSON.parse(content);
  const fields = [];

  if (!Array.isArray(data.authors) || !data.authors.length) {
    data.authors = [{
      name: AUTHOR.name,
      email: AUTHOR.email,
      homepage: AUTHOR.uri,
      role: 'Developer',
    }];
    fields.push('composer authors');
  } else {
    const author = data.authors[0];
    author.name = AUTHOR.name;
    author.email = AUTHOR.email;
    author.homepage = AUTHOR.uri;
    author.role = author.role || 'Developer';
    data.authors[0] = author;
    fields.push('composer authors');
  }

  if (data.homepage !== AUTHOR.homepage) {
    data.homepage = AUTHOR.homepage;
    fields.push('composer homepage');
  }

  if (!data.support) {
    data.support = {};
  }
  const issuesUrl = 'https://github.com/franpass87/FP-Performance/issues';
  if (data.support.issues !== issuesUrl) {
    data.support.issues = issuesUrl;
    fields.push('composer support');
  }

  const description = 'Modular performance suite for shared hosting with caching, asset tuning, WebP conversion, database cleanup, and safe debug tools for WordPress.';
  if (data.description !== description) {
    data.description = description;
    fields.push('composer description');
  }

  const json = `${JSON.stringify(data, null, 4)}\n`;
  if (json === content) {
    return null;
  }

  return { content: json, fields };
}

function normalisePluginHeader(content) {
  const lines = content.split('\n');
  let changed = false;
  const description = 'Modular performance suite for shared hosting with caching, asset tuning, WebP conversion, database cleanup, and safe debug tools.';

  const headerMap = {
    'Plugin URI': AUTHOR.uri,
    Description: description,
    Author: AUTHOR.name,
    'Author URI': AUTHOR.uri,
  };

  const headerFields = [];
  for (let i = 0; i < lines.length; i += 1) {
    const match = lines[i].match(/^ \* ([^:]+):\s*(.*)$/);
    if (match && headerMap[match[1]]) {
      const newLine = ` * ${match[1]}: ${headerMap[match[1]]}`;
      if (lines[i] !== newLine) {
        lines[i] = newLine;
        changed = true;
        headerFields.push(`plugin header ${match[1]}`);
      }
    }
  }

  if (!lines.some((line) => line.startsWith(' * Plugin URI:'))) {
    const index = lines.findIndex((line) => line.includes('Plugin Name:'));
    if (index !== -1) {
      lines.splice(index + 1, 0, ` * Plugin URI: ${AUTHOR.uri}`);
      changed = true;
      headerFields.push('plugin header Plugin URI');
    }
  }

  if (!changed) {
    return null;
  }

  return { content: lines.join('\n'), fields: headerFields };
}

if (!docsOnly) {
  updateFile('fp-performance-suite.php', normalisePluginHeader);
  updateFile('README.md', normaliseReadmeMd);
  updateFile('readme.txt', normaliseReadmeTxt);
  updateFile('composer.json', normaliseComposer);
}

if (docsOnly) {
  updateFile('docs/overview.md', (content) => ({ content, fields: ['docs overview (noop)'] }));
}

if (!report.length) {
  console.log('No metadata changes were necessary.');
  process.exit(0);
}

console.log('\nAuthor metadata synchronised:\n');
console.log('| File | Fields |');
console.log('| --- | --- |');
report.forEach(({ file, fields }) => {
  console.log(`| ${file} | ${fields.join(', ')} |`);
});

if (!apply) {
  console.log('\nRun with --apply or set APPLY=1 to write changes. A .bak file has been written for inspection.');
}
