<?php

namespace OCA\Mail\Tests\Model;

/**
 * ownCloud
 *
 * @author Thomas Müller
 * @copyright 2014 Thomas Müller deepdiver@owncloud.com
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
use Horde_Imap_Client_Data_Fetch;
use Horde_Imap_Client_Fetch_Results;
use Horde_Mime_Part;
use OCA\Mail\Model\IMAPMessage;

class ImapMessageTest extends \PHPUnit_Framework_TestCase {

	public function testNoFrom() {
		$data = new Horde_Imap_Client_Data_Fetch();
		$m = new IMAPMessage(null, 'INBOX', 123, $data);

		$this->assertNull($m->getFrom());
		$this->assertNull($m->getFromEmail());
	}

	public function testGetReplyCcList() {
		$data = new Horde_Imap_Client_Data_Fetch();
		$data->setEnvelope(array(
			'to' => 'a@b.org, tom@example.org, b@example.org',
			'cc' => 'a@b.org, tom@example.org, a@example.org'
		));
		$message = new IMAPMessage(null, 'INBOX', 123, $data);

		$cc = $message->getReplyCcList('a@b.org');
		$this->assertTrue(is_array($cc));
		$this->assertEquals(3, count($cc));
		$cc = array_map(function($item) {
			return $item['email'];
		}, $cc);
		$this->assertTrue(in_array('tom@example.org', $cc));
		$this->assertTrue(in_array('a@example.org', $cc));
		$this->assertTrue(in_array('b@example.org', $cc));
	}

	public function testIconvHtmlMessage() {
		$conn = $this->getMockBuilder('Horde_Imap_Client_Socket')
		->disableOriginalConstructor()
			->setMethods(['fetch'])
			->getMock();

		$urlGenerator = $this->getMockBuilder('\OCP\IURLGenerator')
			->disableOriginalConstructor()
			->getMock();

		//linkToRoute 'mail.proxy.proxy'
		$urlGenerator->expects($this->any())
			->method('linkToRoute')
			->will($this->returnCallback(function ($url) {
				return "https://docs.example.com/server/go.php?to=$url";
			}));
		$htmlService = new \OCA\Mail\Service\Html($urlGenerator);

		// mock first fetch
		$firstFetch = new Horde_Imap_Client_Data_Fetch();
		$firstPart = Horde_Mime_Part::parseMessage(file_get_contents(__DIR__ . '/../data/mail-message-123.txt'), ['level' => 1]);
		$firstFetch->setStructure($firstPart);
		$firstFetch->setBodyPart(1, $firstPart->getPart(1)->getContents());
		$firstFetch->setBodyPart(2, $firstPart->getPart(2)->getContents());
		$firstResult = new Horde_Imap_Client_Fetch_Results();
		$firstResult[123] = $firstFetch;
		$conn->expects($this->any())
			->method('fetch')
			->willReturn($firstResult);


		$message = new IMAPMessage($conn, 'INBOX', 123, null, true, $htmlService);
		$htmlBody = $message->getHtmlBody(0, 0, 123, function() {return null;});
		$this->assertTrue(strlen($htmlBody) > 1000);

		$plainTextBody = $message->getPlainBody();
		$this->assertTrue(strlen($plainTextBody) > 1000);
	}
}

