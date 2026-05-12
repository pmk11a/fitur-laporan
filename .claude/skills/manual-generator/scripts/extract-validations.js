/**
 * Extract validation rules from Laravel Form Request classes
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '../../../..');

/**
 * Extract validations from a Form Request class
 * @param {string} requestName - Request class name (e.g., 'StoreBarangRequest')
 * @returns {object} Parsed validation rules
 */
export function extractValidations(requestName) {
    const requestPath = path.join(projectRoot, 'be-keu/app/Http/Requests', `${requestName}.php`);

    if (!fs.existsSync(requestPath)) {
        console.warn(`Request not found: ${requestPath}`);
        return null;
    }

    const content = fs.readFileSync(requestPath, 'utf-8');

    return {
        className: requestName,
        rules: extractRules(content),
        messages: extractMessages(content),
        attributes: extractAttributes(content),
    };
}

/**
 * Extract validation rules
 */
function extractRules(content) {
    const match = content.match(/rules\(\): array\s*\{([\s\S]*?)\}\s*;/);
    if (!match) return {};

    const rulesContent = match[1];
    const rules = {};

    // Match 'field' => 'rule1|rule2' or 'field' => ['rule1', 'rule2']
    const fieldMatches = rulesContent.matchAll(/['"]([^'"]+)['"]\s*=>(?:\s*\[([\s\S]*?)\]|[\s\S]*?['"]([^'"]+)['"](?:\s*\|)?)/g);

    for (const fieldMatch of fieldMatches) {
        const fieldName = fieldMatch[1];
        const arrayRules = fieldMatch[2];
        const stringRule = fieldMatch[3];

        if (arrayRules) {
            // Array format: ['rule1', 'rule2']
            const ruleList = arrayRules.match(/['"]([^'"]+)['"]/g);
            rules[fieldName] = ruleList
                ? ruleList.map(r => parseRule(r.replace(/['"]/g, '')))
                : [];
        } else if (stringRule) {
            // String format: 'rule1|rule2'
            const ruleList = stringRule.split('|').map(r => r.trim()).filter(r => r);
            rules[fieldName] = ruleList.map(parseRule);
        }
    }

    return rules;
}

/**
 * Parse a single validation rule
 */
function parseRule(ruleString) {
    const parts = ruleString.split(':');
    const ruleName = parts[0];
    const ruleParams = parts[1] || null;

    return {
        rule: ruleName,
        params: ruleParams,
    };
}

/**
 * Extract custom error messages
 */
function extractMessages(content) {
    const match = content.match(/messages\(\): array\s*\{([\s\S]*?)\}\s*;/);
    if (!match) return {};

    const messagesContent = match[1];
    const messages = {};

    const msgMatches = messagesContent.matchAll(/['"]([^'"]+)['"]\s*=>\s*['"]([^'"]+)['"]/g);
    for (const msgMatch of msgMatches) {
        messages[msgMatch[1]] = msgMatch[2];
    }

    return messages;
}

/**
 * Extract custom attribute names
 */
function extractAttributes(content) {
    const match = content.match(/attributes\(\): array\s*\{([\s\S]*?)\}\s*;/);
    if (!match) return {};

    const attrsContent = match[1];
    const attributes = {};

    const attrMatches = attrsContent.matchAll(/['"]([^'"]+)['"]\s*=>\s*['"]([^'"]+)['"]/g);
    for (const attrMatch of attrMatches) {
        attributes[attrMatch[1]] = attrMatch[2];
    }

    return attributes;
}

/**
 * Convert validation rules to human-readable format
 */
export function rulesToHumanReadable(rules) {
    const ruleDescriptions = {
        'required': 'Wajib diisi',
        'nullable': 'Boleh kosong',
        'string': 'Teks',
        'integer': 'Angka bulat',
        'numeric': 'Angka',
        'float': 'Angka desimal',
        'boolean': 'Ya/Tidak',
        'array': 'Daftar',
        'date': 'Tanggal',
        'email': 'Email valid',
        'min': (params) => params ? `Minimal ${params} karakter` : '',
        'max': (params) => params ? `Maksimal ${params} karakter` : '',
        'unique': 'Tidak boleh duplikat',
        'exists': 'Data tidak ditemukan',
        'in': (params) => params ? ` Salah satu dari: ${params}` : '',
        'regex': 'Format tidak valid',
    };

    return rules.map(rule => {
        const desc = ruleDescriptions[rule.rule];
        if (typeof desc === 'function') {
            return desc(rule.params);
        }
        return desc || rule.rule;
    }).filter(Boolean).join(', ');
}

// Export for CLI usage
if (process.argv[1] === fileURLToPath(import.meta.url)) {
    const requestName = process.argv[2];
    if (!requestName) {
        console.error('Usage: node extract-validations.js <RequestName>');
        process.exit(1);
    }

    const result = extractValidations(requestName);
    console.log(JSON.stringify(result, null, 2));
}

export default { extractValidations, rulesToHumanReadable };