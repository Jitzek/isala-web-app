<?php

use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertTrue;

/**
 * UnitTest
 * @group Unit
 */

// ClassName and FileName needs to end with "Test" (capitalizing shouldn't matter)
class DocumentsTest extends TestCase
{
    /** @test */
    public function FileLengthTooLong()
    {
        $filename = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz";
        $this->assertFalse($this->checkFileNamelength($filename));
    }
    /** @test */
    public function FileLengthCorrectLength()
    {
        $filename = "ditiseenfilename";
        $this->assertTrue($this->checkFileNamelength($filename));
    }
    /** @test */
    public function FilenameValid()
    {
        $filename = "eengoeiefilename";
        $this->assertTrue($this->checkFileName($filename));
    }
    /** @test */
    public function FilenameValidWithSpaces()
    {
        $filename = "een goeie file name";
        $this->assertTrue($this->checkFileName($filename));
    }
    /** @test */
    public function FilenameInvalidArrows()
    {
        $filename = "test<>";
        $this->assertFalse($this->checkFileName($filename));
    }
    /** @test */
    public function FilenameInvalidBrackets()
    {
        $filename = "test()";
        $this->assertFalse($this->checkFileName($filename));
    }
    /** @test */
    public function FilenameValidNumbers()
    {
        $filename = "123test123";
        $this->assertTrue($this->checkFileName($filename));
    }
    /** @test */
    public function FiletypeValid()
    {
        $FileType_pdf = "pdf";
        $FileType_doc = "doc";
        $FileType_docx = "docx";
        $FileType_pages = "pages";
        $this->assertTrue($this->checkfiletype($FileType_pdf));
        $this->assertTrue($this->checkfiletype($FileType_doc));
        $this->assertTrue($this->checkfiletype($FileType_docx));
        $this->assertTrue($this->checkfiletype($FileType_pages));
    }
    /** @test */
    public function FiletypeInvalid()
    {
        $FileType_exe = "exe";
        $FileType_txt = "txt";
        $FileType_bat = "bat";
        $FileType_com = "com";
        $FileType_jar = "jar";
        $FileType_dll = "dll";
        $FileType_xls = "xls";
        $this->assertFalse($this->checkfiletype($FileType_exe));
        $this->assertFalse($this->checkfiletype($FileType_txt));
        $this->assertFalse($this->checkfiletype($FileType_bat));
        $this->assertFalse($this->checkfiletype($FileType_com));
        $this->assertFalse($this->checkfiletype($FileType_jar));
        $this->assertFalse($this->checkfiletype($FileType_dll));
        $this->assertFalse($this->checkfiletype($FileType_xls));
    }
    /** @test */
    public function checkValidMime()
    {
        $mimedoc = "application/msword";
        $mimepdf = "application/pdf";
        $mimedocx = "application/vnd.openxmlformats-officedocument.wordprocessingml.document";
        $mimepages = "application/x-iwork-pages-sffpages";
        $mimeexcel = "application/vnd.ms-excel";
        $mimeexcelx = "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
        $this->assertTrue($this->checkmtype($mimedoc));
        $this->assertTrue($this->checkmtype($mimepdf));
        $this->assertTrue($this->checkmtype($mimedocx));
        $this->assertTrue($this->checkmtype($mimepages));
        $this->assertTrue($this->checkmtype($mimeexcel));
        $this->assertTrue($this->checkmtype($mimeexcelx));
    }
    /** @test */
    public function checkInvalidMime()
    {
        $mimeempty = "";
        $mimeexe = "application/octet-stream";
        $mimejs = "application/javascript";
        $mimemp3 = "video/x-mpeg";
        $mimepp = "application/mspowerpoint";
        $mimetxt = "text/plain";
        $this->assertFalse($this->checkmtype($mimeempty));
        $this->assertFalse($this->checkmtype($mimeexe));
        $this->assertFalse($this->checkmtype($mimejs));
        $this->assertFalse($this->checkmtype($mimemp3));
        $this->assertFalse($this->checkmtype($mimepp));
        $this->assertFalse($this->checkmtype($mimetxt));
    }
    public function checkFileNamelength($filename)
    {
        if (strlen($filename) > 40) {
            return false;
        } else {
            return true;
        }
    }
    public function checkFileName($filename)
    {
        if (!preg_match("`^[-0-9A-Z_\. ]+$`i", $filename)) {
            return false;
        } else {
            return true;
        }
    }
    public function checkfiletype($FileType)
    {
        if ($FileType != "pdf" && $FileType != "doc" && $FileType != "docx" && $FileType != "pages") {
            return false;
        } else {
            return true;
        }
    }
    public function checkmtype($mtype)
    {
        if (
            $mtype == ("application/msword") ||
            $mtype == ("application/pdf") ||
            $mtype == ("application/vnd.openxmlformats-officedocument.wordprocessingml.document") ||
            $mtype == ("application/x-iwork-pages-sffpages") ||
            $mtype == ("application/vnd.ms-excel") ||
            $mtype == ("application/vnd.openxmlformats-officedocument.spreadsheetml.sheet")
        ) {
            return true;
        } else {
            return false;
        }
    }
}
