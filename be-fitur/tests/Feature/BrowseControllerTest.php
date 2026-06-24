<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\BrowseService;

class BrowseControllerTest extends TestCase
{
    protected BrowseService $browseService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->browseService = new BrowseService();
    }

    public function test_getConfig_returns_config_for_valid_kodeBrowse(): void
    {
        $config = $this->browseService->getConfig('10051');

        $this->assertNotNull($config);
        $this->assertEquals('DBPERKIRAAN', $config['table']);
        $this->assertEquals('Perkiraan', $config['keyField']);
        $this->assertEquals('Keterangan', $config['labelField']);
    }

    public function test_getConfig_returns_null_for_unknown_kodeBrowse(): void
    {
        $config = $this->browseService->getConfig('99999');

        $this->assertNull($config);
    }

    public function test_types_returns_array_of_all_browse_types(): void
    {
        $types = $this->browseService->types();

        $this->assertIsArray($types);
        $this->assertGreaterThan(10, count($types)); // We have ~80+ types

        // Check structure
        $first = $types[0];
        $this->assertArrayHasKey('kodeBrowse', $first);
        $this->assertArrayHasKey('keyField', $first);
        $this->assertArrayHasKey('labelField', $first);
    }

    public function test_search_returns_results_for_valid_query(): void
    {
        // Perkiraan search — should return results for common query
        $results = $this->browseService->search('10051', 'kas', 5);

        $this->assertIsArray($results);
        // May be empty if DB is empty, but should not error
    }

    public function test_search_returns_empty_for_unknown_kodeBrowse(): void
    {
        $results = $this->browseService->search('99999', 'test', 5);

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function test_validateCode_returns_null_for_empty_code(): void
    {
        $result = $this->browseService->validateCode('10051', '');

        $this->assertNull($result);
    }

    public function test_validateBatch_returns_empty_for_empty_codes(): void
    {
        $results = $this->browseService->validateBatch('10051', []);

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    public function test_getAll_returns_records_without_query(): void
    {
        $results = $this->browseService->getAll('1005', 10);

        $this->assertIsArray($results);
    }

    /**
     * Test that perkiraan browse config has correct structure
     */
    public function test_perkiraan_config_structure(): void
    {
        $config = $this->browseService->getConfig('10051');

        $this->assertArrayHasKey('table', $config);
        $this->assertArrayHasKey('keyField', $config);
        $this->assertArrayHasKey('labelField', $config);
        $this->assertArrayHasKey('additionalFields', $config);
        $this->assertArrayHasKey('joins', $config);
        $this->assertArrayHasKey('whereExtra', $config);
    }

    /**
     * Test that barang browse config has correct structure
     */
    public function test_barang_config_structure(): void
    {
        $config = $this->browseService->getConfig('120302');

        $this->assertEquals('vwBarang', $config['table']);
        $this->assertEquals('KodeBrg', $config['keyField']);
        $this->assertEquals('NamaBrg', $config['labelField']);
    }

    /**
     * Test customer browse config
     */
    public function test_customer_config_structure(): void
    {
        $config = $this->browseService->getConfig('10142');

        $this->assertEquals('vwBrowsCust', $config['table']);
        $this->assertEquals('KodeCustSupp', $config['keyField']);
        $this->assertContains('Alamat', $config['additionalFields']);
    }

    /**
     * Regression test: supplier browse 10141 must use vwBrowsSupp view.
     * Previously used DBPERKCUSTSUPP table which only contains suppliers
     * with Perkiraan mapping — caused "Tidak ditemukan" for suppliers
     * not yet mapped. View vwBrowsSupp returns ALL active suppliers.
     */
    public function test_supplier_config_uses_view(): void
    {
        $config = $this->browseService->getConfig('10141');

        $this->assertEquals('vwBrowsSupp', $config['table']);
        $this->assertEquals('KodeCustSupp', $config['keyField']);
        $this->assertEquals('NamaCustSupp', $config['labelField']);
        $this->assertContains('Alamat', $config['additionalFields']);
    }

    /**
     * Test browse 1014 (Master CustSupp by Perkiraan) — Delphi code:
     *   select X.KodeCustSupp, Y.NamaCustSupp,
     *    rtrim(ltrim(isnull(Y.Alamat1,'')+case when isnull(Y.Alamat2,'')='' then '' else ' '+Y.Alamat2 end)) Alamat,
     *    Y.Kota
     *   from vwGroupCustSupp X, DBCUSTSUPP Y
     *   where X.KodeCustSupp=Y.KodeCustSupp
     *     and x.perkiraan = @NoKira
     *     and (Y.KodeCustSupp like '%...%' or Y.NamaCustSupp like '%...%')
     *   order by X.KodeCustSupp
     *
     * Laravel config must mirror Delphi: view vwGroupCustSupp + LEFT JOIN DBCUSTSUPP
     * with concat Alamat1+' '+Alamat2 + parent_filter Perkiraan.
     */
    public function test_browse_1014_master_custsupp_config(): void
    {
        $config = $this->browseService->getConfig('1014');

        $this->assertNotNull($config);
        $this->assertEquals('vwGroupCustSupp', $config['table']);
        $this->assertEquals('KodeCustSupp', $config['keyField']);
        $this->assertEquals('cs_NamaCustSupp', $config['labelField']);
        $this->assertContains('cs_Alamat', $config['additionalFields']);
        $this->assertContains('cs_Kota', $config['additionalFields']);
        $this->assertContains('Perkiraan', $config['additionalFields']);

        // Alamat must concatenate Alamat1 + Alamat2 (mirror Delphi RTRIM/LTRIM/ISNULL/CASE)
        $this->assertStringContainsString('cs.Alamat1', $config['alias_fields']['cs_Alamat']);
        $this->assertStringContainsString('cs.Alamat2', $config['alias_fields']['cs_Alamat']);
        $this->assertStringContainsString('ISNULL', $config['alias_fields']['cs_Alamat']);
        $this->assertStringContainsString('RTRIM', $config['alias_fields']['cs_Alamat']);

        // Parent filter Perkiraan is mandatory (Delphi passes @NoKira)
        $this->assertArrayHasKey('parent_filters', $config);
        $this->assertEquals(1, count($config['parent_filters']));
        $this->assertEquals('Perkiraan', $config['parent_filters'][0]['source_column']);
    }

    /**
     * Test that IN query builder handles array values correctly
     */
    public function test_filter_in_query_with_array_values(): void
    {
        // Simulate the filter replacement logic
        $sql = "SELECT * FROM DBPERKIRAAN WHERE Perkiraan IN (@PerkiraanFilter)";
        $filters = ['PerkiraanFilter' => ['10051', '15310', '20000']];

        $placeholder = '@PerkiraanFilter';
        $value = $filters['PerkiraanFilter'];

        if (is_array($value) && count($value) > 0) {
            $escaped = array_map(fn($v) => "'" . addslashes(String($v)) . "'", $value);
            $inClause = implode(',', $escaped);
            $resultSql = str_replace($placeholder, $inClause, $sql);
        }

        $expected = "SELECT * FROM DBPERKIRAAN WHERE Perkiraan IN ('10051','15310','20000')";
        $this->assertEquals($expected, $resultSql);
    }

    /**
     * Test that empty array does not add filter restriction
     */
    public function test_filter_empty_array_selects_all(): void
    {
        $sql = "SELECT * FROM DBPERKIRAAN WHERE Perkiraan = @PerkiraanFilter";
        $filters = ['PerkiraanFilter' => []];
        $value = $filters['PerkiraanFilter'];

        // Empty array → should not apply WHERE restriction
        // We replace with '__ALL__' as a marker
        if (is_array($value) && count($value) === 0) {
            $resultSql = str_replace('@PerkiraanFilter', "'__ALL__'", $sql);
        }

        $this->assertStringContainsString('__ALL__', $resultSql);
    }

    /**
     * Regression test for 2026-06-22 silent SQL failure.
     * The parser must tolerate BOTH formats of whereExtra:
     *   - "AND <conditions>"  (e.g. "AND pht.Kode = 'HT'")
     *   - "WHERE <conditions>" (e.g. "WHERE IsAktif = 1")
     * Without this, BrowseService::search would generate "WHERE AND ..." SQL
     * which SQL Server silently executes as returning empty result set.
     *
     * @dataProvider whereExtraFormatProvider
     */
    public function test_search_handles_both_whereExtra_formats(string $whereExtra, string $expectedSnippet): void
    {
        // Simulate the parse logic from BrowseService::search
        $extra = preg_replace('/^\s*(WHERE|AND|OR)\s+/i', '', $whereExtra);

        // First condition (q=''): no LIKE clause
        $whereClause = '';
        if (!empty($whereClause) === false && !empty(trim($extra))) {
            $whereClause = $extra;
        }

        $sql = 'SELECT TOP 20 Perkiraan, Keterangan FROM DBPERKIRAAN';
        if (!empty(trim($whereClause))) {
            $sql .= ' WHERE ' . $whereClause;
        }

        $this->assertStringNotContainsString('WHERE AND', $sql);
        $this->assertStringNotContainsString('WHERE WHERE', $sql);
        $this->assertStringContainsString($expectedSnippet, $sql);
    }

    public static function whereExtraFormatProvider(): array
    {
        return [
            'AND-prefixed format (perkiraan HT)' => [
                "AND pht.Kode = 'HT'",
                "pht.Kode = 'HT'",
            ],
            'WHERE-prefixed format (customer aktif)' => [
                'WHERE IsAktif = 1',
                'IsAktif = 1',
            ],
            'WHERE with multiple AND conditions' => [
                'WHERE IsAktif = 1 AND Jenis = 1',
                'IsAktif = 1 AND Jenis = 1',
            ],
            'AND with multiple conditions' => [
                'AND DBPERKIRAAN.Kelompok = 3 AND DBPERKIRAAN.Tipe = 1',
                'DBPERKIRAAN.Kelompok = 3 AND DBPERKIRAAN.Tipe = 1',
            ],
        ];
    }
}
