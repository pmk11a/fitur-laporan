/**
 * Parse Laravel Eloquent Model
 * Extracts table name, fillable fields, relationships from Model files
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '../../../..');

/**
 * Parse a Laravel Eloquent Model and extract metadata
 * @param {string} modelName - Model name (e.g., 'BARANG', 'JUAL')
 * @returns {object} Parsed model metadata
 */
export function parseModel(modelName) {
    const modelPath = path.join(projectRoot, 'be-keu/app/Models', `${modelName}.php`);

    if (!fs.existsSync(modelPath)) {
        console.warn(`Model not found: ${modelPath}`);
        return null;
    }

    const content = fs.readFileSync(modelPath, 'utf-8');

    return {
        name: modelName,
        table: extractTableName(content),
        fillable: extractFillable(content),
        guarded: extractGuarded(content),
        casts: extractCasts(content),
        primaryKey: extractPrimaryKey(content),
        timestamps: extractTimestamps(content),
        relationships: extractRelationships(content),
        scopes: extractScopes(content),
    };
}

/**
 * Extract table name from model
 */
function extractTableName(content) {
    const match = content.match(/\$table\s*=\s*['"]([^'"]+)['"]/);
    return match ? match[1] : null;
}

/**
 * Extract fillable fields
 */
function extractFillable(content) {
    const match = content.match(/\$fillable\s*=\s*\[([\s\S]*?)\];/);
    if (!match) return [];

    const fillableContent = match[1];
    const fields = fillableContent.match(/['"]([^'"]+)['"]/g);
    return fields ? fields.map(f => f.replace(/['"]/g, '')) : [];
}

/**
 * Extract guarded fields
 */
function extractGuarded(content) {
    const match = content.match(/\$guarded\s*=\s*\[([\s\S]*?)\];/);
    if (!match) return [];

    const guardedContent = match[1];
    const fields = guardedContent.match(/['"]([^'"]+)['"]/g);
    return fields ? fields.map(f => f.replace(/['"]/g, '')) : [];
}

/**
 * Extract casts
 */
function extractCasts(content) {
    const match = content.match(/\$casts\s*=\s*\[([\s\S]*?)\];/);
    if (!match) return {};

    const castsContent = match[1];
    const casts = {};

    const lines = castsContent.split(',');
    for (const line of lines) {
        const kvMatch = line.match(/['"]([^'"]+)['"]\s*=>\s*['"]([^'"]+)['"]/);
        if (kvMatch) {
            casts[kvMatch[1]] = kvMatch[2];
        }
    }

    return casts;
}

/**
 * Extract primary key
 */
function extractPrimaryKey(content) {
    const match = content.match(/\$primaryKey\s*=\s*['"]([^'"]+)['"]/);
    return match ? match[1] : 'id';
}

/**
 * Extract timestamps configuration
 */
function extractTimestamps(content) {
    const match = content.match(/\$timestamps\s*=\s*(true|false)/);
    return match ? match[1] === 'true' : true;
}

/**
 * Extract relationships
 */
function extractRelationships(content) {
    const relationships = [];

    // HasOne
    const hasOneMatches = content.matchAll(/function\s+(\w+)\(\)\s*->\s*hasOne\(([^,]+)(?:,\s*([^)]+))?\)/g);
    for (const match of hasOneMatches) {
        relationships.push({
            type: 'hasOne',
            name: match[1],
            model: match[2].trim().replace(/['"]/g, ''),
            foreignKey: match[3]?.trim().replace(/['"]/g, '') || null,
        });
    }

    // HasMany
    const hasManyMatches = content.matchAll(/function\s+(\w+)\(\)\s*->\s*hasMany\(([^,]+)(?:,\s*([^)]+))?\)/g);
    for (const match of hasManyMatches) {
        relationships.push({
            type: 'hasMany',
            name: match[1],
            model: match[2].trim().replace(/['"]/g, ''),
            foreignKey: match[3]?.trim().replace(/['"]/g, '') || null,
        });
    }

    // BelongsTo
    const belongsToMatches = content.matchAll(/function\s+(\w+)\(\)\s*->\s*belongsTo\(([^,]+)(?:,\s*([^)]+))?\)/g);
    for (const match of belongsToMatches) {
        relationships.push({
            type: 'belongsTo',
            name: match[1],
            model: match[2].trim().replace(/['"]/g, ''),
            foreignKey: match[3]?.trim().replace(/['"]/g, '') || null,
        });
    }

    // BelongsToMany
    const belongsToManyMatches = content.matchAll(/function\s+(\w+)\(\)\s*->\s*belongsToMany\(([^,]+)(?:,\s*([^)]+))?\)/g);
    for (const match of belongsToManyMatches) {
        relationships.push({
            type: 'belongsToMany',
            name: match[1],
            model: match[2].trim().replace(/['"]/g, ''),
            pivotTable: match[3]?.trim().replace(/['"]/g, '') || null,
        });
    }

    return relationships;
}

/**
 * Extract scopes
 */
function extractScopes(content) {
    const scopes = [];
    const scopeMatches = content.matchAll(/function\s+scope(\w+)\([^)]+\)/g);

    for (const match of scopeMatches) {
        scopes.push(match[1].charAt(0).toLowerCase() + match[1].slice(1));
    }

    return scopes;
}

/**
 * Map field type from cast or naming convention
 */
export function mapFieldType(fieldName, casts) {
    if (casts[fieldName]) {
        const castMap = {
            'integer': 'number',
            'float': 'number',
            'double': 'number',
            'decimal': 'currency',
            'boolean': 'checkbox',
            'array': 'array',
            'object': 'json',
            'datetime': 'datetime',
            'date': 'date',
            'timestamp': 'timestamp',
        };
        return castMap[casts[fieldName]] || 'text';
    }

    // Naming convention hints
    const lowerName = fieldName.toLowerCase();
    if (lowerName.includes('price') || lowerName.includes('harga') || lowerName.includes('amount') || lowerName.includes('total')) {
        return 'currency';
    }
    if (lowerName.includes('date') || lowerName.includes('tanggal')) {
        return 'date';
    }
    if (lowerName.includes('qty') || lowerName.includes('quantity') || lowerName.includes('jumlah') || lowerName.includes('stok')) {
        return 'number';
    }
    if (lowerName.includes('desc') || lowerName.includes('description') || lowerName.includes('nama') || lowerName.includes('name')) {
        return 'text';
    }
    if (lowerName.includes('code') || lowerName.includes('kode')) {
        return 'text';
    }

    return 'text';
}

// Export for CLI usage
if (process.argv[1] === fileURLToPath(import.meta.url)) {
    const modelName = process.argv[2];
    if (!modelName) {
        console.error('Usage: node parse-model.js <ModelName>');
        process.exit(1);
    }

    const result = parseModel(modelName);
    console.log(JSON.stringify(result, null, 2));
}

export default { parseModel, mapFieldType };