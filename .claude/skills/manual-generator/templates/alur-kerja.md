# Alur Kerja: {{workflowName}}

## Deskripsi

{{description}}

## Flowchart

```mermaid
{{flowchart}}
```

## Langkah-Langkah

{{#each steps}}
### Langkah {{this.step}}: {{this.title}}

**Aksi:** {{this.action}}

{{#if this.prerequisites}}
**Prasyarat:**
{{#each this.prerequisites}}
- [ ] {{this}}
{{/each}}
{{/if}}

{{#if this.apiEndpoint}}
**API Endpoint:** `{{this.apiEndpoint}}`
{{/if}}

{{#if this.authorizationRequired}}
:::warning Otorisasi Diperlukan
Langkah ini memerlukan otorisasi level {{this.authorizationLevel}}.
:::
{{/if}}

{{#if this.screenshot}}
![Screenshot]({{this.screenshot}})
{{/if}}

{{/each}}

## Skenario Alur Kerja

### Skenario Normal: {{normalScenario.name}}

**Langkah:**
1. {{normalScenario.steps}}

**Hasil:** {{normalScenario.outcome}}

{{#if authorizationScenario}}
### Skenario dengan Otorisasi: {{authorizationScenario.name}}

**Langkah:**
1. {{authorizationScenario.steps}}

**Hasil:** {{authorizationScenario.outcome}}
{{/if}}

### Skenario Error: {{errorScenario.name}}

**Langkah:**
1. {{errorScenario.steps}}

**Hasil:** {{errorScenario.outcome}}

## Waktu Pengerjaan

| Langkah | Estimasi Waktu |
|---------|----------------|
{{#each steps}}
| {{this.step}} | {{this.estimatedTime}} |
{{/each}}

**Total Estimasi:** {{totalEstimatedTime}}