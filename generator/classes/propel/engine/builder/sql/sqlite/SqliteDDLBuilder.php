<?php

/*
 *  $Id: OMBuilder.php 186 2005-09-08 13:33:09Z hans $
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://propel.phpdb.org>.
 */

require_once 'propel/engine/builder/sql/DDLBuilder.php';

/**
 * 
 * 
 * 
 * @author Hans Lellelid <hans@xmpl.org>
 * @package propel.engine.builder.sql.pgsql
 */
class SqliteDDLBuilder extends DDLBuilder {
		
	/**
	 * 
	 * @see parent::addDropStatement()
	 */
	protected function addDropStatements(&$script)
	{
		$table = $this->getTable();
		$script .= "
DROP TABLE ".$table->getName().";
";
	}
	
	/**
	 * 
	 * @see parent::addColumns()
	 */
	protected function addTable(&$script)
	{
		$table = $this->getTable();
		$script .= "
-----------------------------------------------------------------------------
-- ".$table->getName()."
-----------------------------------------------------------------------------
";

		$this->addDropStatements($script);

		$script .= "

CREATE TABLE ".$table->getName()." 
(
	";
	
		$lines = array();
		
		foreach ($table->getColumns() as $col) {
			$type = $col->getDomain()->getSqlType();
			if ($col->isAutoIncrement()) {
				$entry = $col->getName() . " " . $col->getAutoIncrementString();
			} else {
				$size = $col->printSize();
				$default = $col->getDefaultSetting();
				$entry = $col->getName() . " $type $size $default " . $col->getNotNullString() . " " . $col->getAutoIncrementString();
			}
			
			$lines[] = trim($entry);
			
		}

		foreach ($table->getUnices() as $unique ) { 
			$lines[] = "UNIQUE (".$unique->getColumnList().")";
    	}

		$sep = ",
	";
		$script .= implode($sep, $lines);
		$script .= "
);
";
	}
	
	/**
	 * Adds CREATE INDEX statements for this table.
	 * @see parent::addIndices()
	 */
	protected function addIndices(&$script)
	{
		$table = $this->getTable();
		foreach ($table->getIndices() as $index) {
			$script .= "
CREATE ";
			if($index->getIsUnique()) {
				$script .= "UNIQUE";
			}
			$script .= "INDEX ".$index->getName() ." ON ".$table->getName()." (".$index->getColumnList().");
";
		}
	}

	/**
	 * 
	 * @see parent::addForeignKeys()
	 */
	protected function addForeignKeys(&$script)
	{
		$table = $this->getTable();
		foreach ($table->getForeignKeys() as $fk) {
			$script .= "
-- SQLite does not support foreign keys; this is just for reference
-- FOREIGN KEY (".$fk->getLocalColumnNames().") REFERENCES ".$fk->getForeignTableName()." (".$fk->getForeignColumnNames().")
";
		}
	}
	
}