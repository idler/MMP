<?php

class SchemaGenerationTest extends dbTestCase
{
  public function testSimpleSchemaCreation()
  {
    $this->buildSchema();
    $this->assertNoMigration();
  }

  public function testSimpleOneMigration()
  {
    $this->buildSchema();
    $this->query("alter table test add column newCol INT");
    $this->buildMigration();
    $this->assertNoMigration();
  }
}
