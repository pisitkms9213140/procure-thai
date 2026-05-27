<?php

namespace App\Imports;

/**
 * No-concern import used with Excel::toArray() to read a worksheet as raw,
 * numerically-indexed rows (row 0 = the header labels). We read by column
 * INDEX rather than heading keys so arbitrary / duplicate / Thai headers from
 * SAP exports survive, and the user-defined column mapping can resolve them.
 */
class RawSheetImport
{
}
