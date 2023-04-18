<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * A dummy SMTP server that does not actually send mails.
 *
 * TODO move this file to another location (it is used for testing)
 */
class FakeSMTP
{
    /**
     * The servers ip address.
     *
     * @var string  Defaults to '127.0.0.1'.
     */
    protected $host = '127.0.0.1';

    /**
     * The tcp port to listen.
     *
     * @var int  Defaults to 25000.
     */
    protected $port = 25000;

    /**
     * Holds the network socket of the server.
     *
     * @var resource  Defaults to null.
     */
    protected $socket;

    /**
     * Configures php environment, creates and binds network socket.
     */
    public function __construct()
    {
        set_time_limit(0);
        error_reporting(E_ERROR);
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0);
        socket_bind($this->socket, $this->host, $this->port);
    }

    /**
     * Handles incoming connection requests.
     */
    public function waitForConnections()
    {
        socket_listen($this->socket, 3);
        do {
            $client = socket_accept($this->socket);
            $this->startSmtpSession($client);
        } while (true);
    }

    /**
     * Sends a response to a client.
     *
     * @param resource $socket   The receiving network socket.
     * @param string   $response The message to send.
     */
    protected function sendSMTPResponse($socket, $response)
    {
        $response .= "\n";
        socket_write($socket, $response, strlen($response));
        echo "> $response";
    }

    /**
     * Handles incoming SMTP requests.
     *
     * @param resource $socket  The client socket.
     * @param string   $request Incoming request.
     * @return bool True if the request terminates a connection.
     */
    protected function handleSMTPRequest($socket, $request)
    {
        $cont = true;
        switch (substr($request, 0, 4)) {
            case 'HELO':
                $response = '250 OK';
                $this->sendSMTPResponse($socket, $response);
                break;
            case 'MAIL':
                $response = '250 Sender OK';
                $this->sendSMTPResponse($socket, $response);
                break;
            case 'RCPT':
                $response = '250 Recipient OK';
                $this->sendSMTPResponse($socket, $response);
                break;
            case 'DATA':
                $response = '354 End data with <CR><LF>.<CR><LF>';
                $this->sendSMTPResponse($socket, $response);
                do {
                    $input = socket_read($socket, 1024, 1);
                    if ($input !== false && trim($input) !== '') {
                        echo "< $input\n";
                    }
                } while ($socket && trim($input) !== ".");
                $response = '250 Message accepted for delivery';
                $this->sendSMTPResponse($socket, $response);
                break;
            case 'QUIT':
                $response = '221 Bye';
                $this->sendSMTPResponse($socket, $response);
                $cont = false;
                break;
            case 'RSET':
                $response = '250 Ok';
                $this->sendSMTPResponse($socket, $response);
                break;
            default:
                $response = '500 Command not recognized: Syntax error.';
                $this->sendSMTPResponse($socket, $response);
                break;
        }
        return $cont;
    }

    /**
     * Starts an SMTP session for a new client connection.
     *
     * @param resource $client The client network socket.
     */
    protected function startSmtpSession($client)
    {
        $this->sendSMTPResponse($client, "220 $host SMTP rabooF Mailserver"); // TODO bug $host
        do {
            $exit = false;
            // read client input
            if ($input = socket_read($client, 1024, 1)) {
                if ($input !== false && trim($input) !== '') {
                    echo "< $input\n";
                    $exit = ! $this->handleSMTPRequest($client, $input);
                }
            } else {
                $exit = true;
            }
        } while (! $exit);
        echo "Connection terminated.\n\n";
        socket_close($client);
    }
}

// Start fake SMTP server
$smtp = new FakeSMTP();
$smtp->waitForConnections();
