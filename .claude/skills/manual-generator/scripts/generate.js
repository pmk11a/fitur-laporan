/**
 * Generate Manual Book from Laravel code
 *
 * Usage:
 *   node generate.js 02-01-barang       # Generate single module
 *   node generate.js --all               # Generate all modules
 */

import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import { parseModel, mapFieldType } from './parse-model.js';
import { extractValidations, rulesToHumanReadable } from './extract-validations.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '../../../..');

// Load module config
const configPath = path.join(__dirname, '../config/module-config.json');
const moduleConfig = JSON.parse(fs.readFileSync(configPath, 'utf-8'));

// Template paths
const templatesDir = path.join(__dirname, '../templates');

/**
 * Load a template file
 */
function loadTemplate(templateName) {
    const templatePath = path.join(templatesDir, templateName);
    return fs.readFileSync(templatePath, 'utf-8');
}

/**
 * Simple mustache-like template rendering
 */
function renderTemplate(template, data) {
    let result = template;

    // Replace simple variables
    for (const [key, value] of Object.entries(data)) {
        if (value !== undefined && value !== null) {
            if (Array.isArray(value)) {
                // Handle arrays with {{#each}} syntax
                const eachPattern = new RegExp(`\\{\\{#each\\s+${key}\\}\\}([\\s\\S]*?)\\{\\{/each\\}\\}`, 'g');
                result = result.replace(eachPattern, (match, innerTemplate) => {
                    if (value.length === 0) return '';
                    return value.map(item => {
                        let innerResult = innerTemplate;
                        for (const [k, v] of Object.entries(item)) {
                            innerResult = innerResult.replace(new RegExp(`\\{\\{this\\.${k}\\}\\}`, 'g'), v ?? '');
                            // Handle conditional for required
                            innerResult = innerResult.replace(/\{\{#if\s+this\.(\w+)\}\}([\s\S]*?)\{\{\/if\}\}/g, (m, prop, content) => {
                                return item[prop] ? content : '';
                            });
                        }
                        return innerResult;
                    }).join('');
                });

                // Handle simple array items
                result = result.replace(new RegExp(`\\{\\{${key}\\}\\}`, 'g'), value.join(', '));
            } else if (typeof value === 'object') {
                // Handle nested objects
                for (const [nestedKey, nestedValue] of Object.entries(value)) {
                    result = result.replace(new RegExp(`\\{\\{${key}\\.${nestedKey}\\}\\}`, 'g'), nestedValue ?? '');
                }
            } else {
                // Simple replace
                result = result.replace(new RegExp(`\\{\\{${key}\\}\\}`, 'g'), String(value));
            }
        }
    }

    // Remove any remaining {{#each}} blocks
    result = result.replace(/\{\{#each\s+\w+\}\}[\s\S]*?\{\{\/each\}\}/g, '');

    // Remove any remaining {{}} placeholders
    result = result.replace(/\{\{[^}]+\}\}/g, '');

    return result;
}

/**
 * Format validation rules for display
 */
function formatValidations(rules) {
    if (!rules || rules.length === 0) return '-';
    return rules.map(r => {
        if (r.params) {
            return `${r.rule}:${r.params}`;
        }
        return r.rule;
    }).join(', ');
}

/**
 * Generate field definitions from model and validations
 */
function generateFields(modelData, validationData) {
    if (!validationData || !validationData.rules) {
        return modelData.fillable.map(field => ({
            fieldId: field,
            label: formatFieldLabel(field),
            type: mapFieldType(field, modelData.casts || {}),
            required: false,
            validations: [],
            description: '',
        }));
    }

    return modelData.fillable.map(field => {
        const rules = validationData.rules[field] || [];
        const isRequired = rules.some(r => r.rule === 'required');
        const maxRule = rules.find(r => r.rule === 'max');
        const minRule = rules.find(r => r.rule === 'min');

        return {
            fieldId: field,
            label: formatFieldLabel(field),
            type: mapFieldType(field, modelData.casts || {}),
            required: isRequired,
            validations: rules.map(r => ({
                rule: r.rule,
                params: r.params,
                message: getValidationMessage(r, field),
            })),
            description: getFieldDescription(field),
        };
    });
}

/**
 * Format field name to human-readable label
 */
function formatFieldLabel(fieldName) {
    // Remove prefixes like KODE, NAMA, ISI, HRG, QNT
    let label = fieldName
        .replace(/^KODE/, '')
        .replace(/^NAMA/, '')
        .replace(/^ISI/, 'Isi ')
        .replace(/^HRG/, 'Harga ')
        .replace(/^QNT/, 'Jumlah ')
        .replace(/([A-Z])/g, ' $1') // Add space before capitals
        .trim();

    // Title case
    return label.charAt(0).toUpperCase() + label.slice(1).toLowerCase();
}

/**
 * Get validation error message
 */
function getValidationMessage(rule, fieldName) {
    const messages = {
        'required': 'Field ini wajib diisi',
        'unique': 'Nilai sudah ada, gunakan nilai lain',
        'exists': 'Data tidak ditemukan',
        'numeric': 'Harus berupa angka',
        'min': `Minimal ${rule.params} karakter`,
        'max': `Maksimal ${rule.params} karakter`,
    };

    const label = formatFieldLabel(fieldName);
    return messages[rule.rule] || `Validasi ${rule.rule} gagal`;
}

/**
 * Get field description
 */
function getFieldDescription(fieldName) {
    const descriptions = {
        'KODEBRG': 'Kode unik untuk identifikasi barang',
        'NAMABRG': 'Nama lengkap barang',
        'KODEGRP': 'Kelompok barang',
        'KODESUPP': 'Supplier utama barang',
        'HARGA': 'Harga jual',
        'QntMin': 'Batas minimum stok untuk alert',
        'QntMax': 'Batas maksimum stok',
    };

    return descriptions[fieldName] || '';
}

/**
 * Generate flowchart mermaid code
 */
function generateFlowchart(moduleId, requiresAuth = false) {
    let flowchart = `flowchart TD
    A([Mulai]) --> B{Hak Akses?}
    B -->|Ya| C{Has Access?}
    B -->|Tidak| Z([Akses Ditolak])

    C -->|Tambah| D{Lihat Form?}
    C -->|Edit| E{Lihat Data?}
    D -->|Ya| F[Form Tambah/Edit]
    E -->|Ya| G[Form Edit]
    F --> H{Data Valid?}
    G --> H
    H -->|Tidak| I[Tampilkan Error]
    I --> F`;

    if (requiresAuth) {
        flowchart += `
    H -->|Ya| J{Mbutuhkan Otorisasi?}
    J -->|Ya| K[Submit untuk Otorisasi]
    K --> L{Otorisasi Disetujui?}
    L -->|Tidak| M([Ditolak])
    L -->|Ya| N[Simpan ke Database]`;
    } else {
        flowchart += `
    H -->|Ya| N[Simpan ke Database]`;
    }

    flowchart += `
    N --> O[Berhasil]
    O --> P([Selesai])`;

    return flowchart;
}

/**
 * Generate a module manual
 */
function generateModule(moduleId) {
    const config = moduleConfig[moduleId];
    if (!config) {
        console.error(`Module config not found: ${moduleId}`);
        return false;
    }

    console.log(`Generating manual for: ${config.moduleNameId}`);

    // Parse model
    const modelData = parseModel(config.model);

    // Extract validations
    const validationData = extractValidations(config.storeRequest);

    // Generate fields
    const fields = generateFields(modelData, validationData);

    // Generate common errors
    const commonErrors = generateCommonErrors(config.errorPrefix, fields);

    // Output directory
    const outputDir = path.join(projectRoot, 'docs/manual-book/docs', moduleId);
    fs.mkdirSync(outputDir, { recursive: true });

    // Generate README.md
    const readmeTemplate = loadTemplate('module-readme.md');
    const readmeData = {
        moduleId: config.moduleId,
        moduleNameId: config.moduleNameId,
        moduleNameEn: config.moduleNameEn,
        lastUpdated: new Date().toISOString(),
        delphiFile: config.delphiFile,
        dbTables: JSON.stringify(config.dbTables),
        permissions: config.permissions,
        masterData: config.masterData,
        prerequisiteModules: config.prerequisiteModules,
        overview: config.overview,
        navigationPath: config.navigationPath,
    };
    fs.writeFileSync(path.join(outputDir, 'README.md'), renderTemplate(readmeTemplate, readmeData));

    // Generate form-input.md
    const formTemplate = loadTemplate('form-input.md');
    const formData = {
        formName: config.moduleNameId,
        purpose: `Form ini digunakan untuk menambah atau mengedit data ${config.moduleNameId.toLowerCase()}.`,
        fields: fields,
        commonErrors: commonErrors,
        exampleInput: generateExampleInput(fields),
        tips: generateTips(config.moduleId),
    };
    fs.writeFileSync(path.join(outputDir, 'form-input.md'), renderTemplate(formTemplate, formData));

    // Generate alur-kerja.md
    const workflowTemplate = loadTemplate('alur-kerja.md');
    const workflowData = {
        workflowName: `Alur Kerja ${config.moduleNameId}`,
        description: `Panduan langkah demi langkah untuk menggunakan modul ${config.moduleNameId}.`,
        flowchart: generateFlowchart(config.moduleId, config.requiresAuthorization),
        steps: generateWorkflowSteps(config),
        normalScenario: generateNormalScenario(config),
        authorizationScenario: config.requiresAuthorization ? generateAuthScenario(config) : null,
        errorScenario: generateErrorScenario(),
    };
    fs.writeFileSync(path.join(outputDir, 'alur-kerja.md'), renderTemplate(workflowTemplate, workflowData));

    // Generate error-handling.md
    const errorTemplate = loadTemplate('error-handling.md');
    const errorData = {
        moduleName: config.moduleNameId,
        errorPrefix: config.errorPrefix,
        validationErrors: generateValidationErrors(config.errorPrefix),
        authorizationErrors: generateAuthorizationErrors(config.errorPrefix),
        dataErrors: generateDataErrors(config.errorPrefix),
        systemErrors: generateSystemErrors(config.errorPrefix),
    };
    fs.writeFileSync(path.join(outputDir, 'error-handling.md'), renderTemplate(errorTemplate, errorData));

    console.log(`Generated: ${outputDir}`);
    return true;
}

/**
 * Generate example input
 */
function generateExampleInput(fields) {
    const example = {};
    fields.forEach(field => {
        switch (field.type) {
            case 'text':
                example[field.fieldId] = `Contoh ${field.label}`;
                break;
            case 'number':
            case 'currency':
                example[field.fieldId] = 0;
                break;
            case 'date':
                example[field.fieldId] = '2026-01-01';
                break;
            case 'checkbox':
                example[field.fieldId] = true;
                break;
            default:
                example[field.fieldId] = null;
        }
    });
    return JSON.stringify(example, null, 2);
}

/**
 * Generate tips
 */
function generateTips(moduleId) {
    const tips = {
        '02-01-barang': [
            'Gunakan kode barang yang unik dan mudah diingat',
            'Pilih kelompok yang sesuai untuk memudahkan pencarian',
            'Isi harga dengan benar karena akan digunakan di transaksi',
            'Aktifkan flag "Aktif" agar barang muncul di transaksi',
        ],
        '03-01-penjualan': [
            'Pastikan customer sudah terdaftar sebelum transaksi',
            'Cek stok barang sebelum menambahkan ke penjualan',
            'Validasi harga sudah benar sebelum simpan',
        ],
    };
    return tips[moduleId] || ['Pastikan semua data sudah benar sebelum menyimpan.'];
}

/**
 * Generate workflow steps
 */
function generateWorkflowSteps(config) {
    return [
        {
            step: 1,
            title: 'Akses Menu',
            action: `Navigasi ke ${config.navigationPath}`,
            prerequisites: ['Login ke sistem', 'Punya hak akses modul'],
            estimatedTime: '30 detik',
        },
        {
            step: 2,
            title: 'Tambah/Edit Data',
            action: config.moduleId.startsWith('03')
                ? 'Klik tombol "Transaksi Baru" atau pilih data untuk edit'
                : 'Klik tombol "+ Tambah" atau pilih data untuk edit',
            prerequisites: config.prerequisiteModules.length > 0
                ? ['Modul pendukung sudah ada datanya']
                : [],
            authorizationRequired: config.requiresAuthorization,
            authorizationLevel: config.requiresAuthorization ? 1 : null,
            estimatedTime: '1-5 menit',
        },
        {
            step: 3,
            title: 'Simpan Data',
            action: 'Klik tombol "Simpan" untuk menyimpan data',
            prerequisites: ['Semua field wajib sudah diisi'],
            estimatedTime: '1 detik',
        },
    ];
}

/**
 * Generate scenarios
 */
function generateNormalScenario(config) {
    return {
        name: 'Input Data Berhasil',
        steps: 'Buka form → Isi data → Klik Simpan → Data tersimpan',
        outcome: 'Data berhasil disimpan dan muncul di list',
    };
}

function generateAuthScenario(config) {
    return {
        name: 'Transaksi dengan Otorisasi',
        steps: 'Input data → Submit → Manager otorisasi → Transaksi aktif',
        outcome: 'Transaksi disetujui dan posted ke sistem',
    };
}

function generateErrorScenario() {
    return {
        name: 'Input dengan Error',
        steps: 'Input data → Validasi gagal → Perbaiki error → Submit ulang',
        outcome: 'Error ditampilkan, user bisa perbaiki dan submit lagi',
    };
}

/**
 * Generate error lists
 */
function generateCommonErrors(prefix, fields) {
    return fields
        .filter(f => f.required)
        .map((f, i) => ({
            field: f.label,
            message: `${f.label} wajib diisi`,
            cause: 'Field kosong saat submit',
            solution: 'Isi field dan submit lagi',
        }));
}

function generateValidationErrors(prefix) {
    return [
        { code: `${prefix}-VALID-001`, message: 'Field wajib diisi', cause: 'Field kosong', solution: 'Isi field yang ditandai' },
        { code: `${prefix}-VALID-002`, message: 'Format tidak valid', cause: 'Input tidak sesuai format', solution: 'Perbaiki format input' },
        { code: `${prefix}-VALID-003`, message: 'Data sudah ada', cause: 'Kode duplikat', solution: 'Gunakan kode lain' },
    ];
}

function generateAuthorizationErrors(prefix) {
    return [
        { code: `${prefix}-AUTH-001`, message: 'Tidak punya hak akses', cause: 'Role tidak有权', solution: 'Hubungi admin untuk akses' },
        { code: `${prefix}-AUTH-002`, message: 'Otorisasi ditolak', cause: 'Manager menolak', solution: 'Perbaiki data atau minta persetujuan lain' },
    ];
}

function generateDataErrors(prefix) {
    return [
        { code: `${prefix}-DATA-001`, message: 'Data tidak ditemukan', cause: 'Foreign key invalid', solution: 'Pilih data yang valid' },
        { code: `${prefix}-DATA-002`, message: 'Stok tidak cukup', cause: 'QTY melebihi stok', solution: 'Kurangi jumlah atau cek stok' },
    ];
}

function generateSystemErrors(prefix) {
    return [
        { code: `${prefix}-SYS-001`, message: 'Koneksi gagal', cause: 'Server timeout', solution: 'Coba lagi dalam beberapa menit' },
        { code: `${prefix}-SYS-002`, message: 'Session expired', cause: 'Login timeout', solution: 'Logout dan login lagi' },
    ];
}

// Main execution
const args = process.argv.slice(2);

if (args.includes('--all')) {
    console.log('Generating all modules...');
    let success = 0;
    let failed = 0;

    for (const moduleId of Object.keys(moduleConfig)) {
        if (generateModule(moduleId)) {
            success++;
        } else {
            failed++;
        }
    }

    console.log(`\nDone: ${success} success, ${failed} failed`);
} else if (args.length > 0) {
    const moduleId = args[0];
    generateModule(moduleId);
} else {
    console.log('Usage:');
    console.log('  node generate.js 02-01-barang    # Generate single module');
    console.log('  node generate.js --all            # Generate all modules');
}