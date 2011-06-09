InviteMySQLDAO
==============
Inherits from `PDODAO <./PDODAO.html>`_.

There is no documentation for this class.



Methods
-------

getInviteCode
~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function getInviteCode($invite_code) {
            $q = "SELECT invite_code FROM #prefix#invites WHERE invite_code=:invite_code";
            $vars = array(
                ':invite_code'=>$invite_code
            );
            $ps = $this->execute($q, $vars);
            return $this->getDataRowAsArray($ps);
        }


addInviteCode
~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function addInviteCode($invite_code) {
            if (!$this->doesInviteExist($invite_code)) {
                $q = "INSERT INTO #prefix#invites SET invite_code=:invite_code, created_time=NOW() ";
                $vars = array(
                    ':invite_code'=>$invite_code
                );
                $ps = $this->execute($q, $vars);
                return $this->getUpdateCount($ps);
            } else {
                return 0;
            }
        }


doesInviteExist
~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function doesInviteExist($invite_code) {
            $q = "SELECT invite_code FROM #prefix#invites WHERE invite_code=:invite_code";
            $vars = array(
                ':invite_code'=>$invite_code
            );
            $ps = $this->execute($q, $vars);
            return $this->getDataIsReturned($ps);
        }


isInviteValid
~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function isInviteValid( $invite_code) {
            if ($this->doesInviteExist($invite_code)) {
                $q = " SELECT created_time FROM #prefix#invites WHERE invite_code=:invite_code";
                $vars = array(
                    ':invite_code'=>$invite_code
                );
                $ps = $this->execute($q, $vars);
                $result = $this->getDataRowAsArray( $ps ) ;
                // check to see if the time-stamp is less then 7 days old.
                $qdate = strtotime( $result['created_time'] );
                if ( (time() - $qdate ) <= 648600 ) {
                    return true;
                } else {
                    return false;
                }
            }
            return false;
        }


deleteInviteCode
~~~~~~~~~~~~~~~~



.. code-block:: php5

    <?php
        public function deleteInviteCode($invite_code) {
            $q  = "DELETE FROM #prefix#invites WHERE invite_code=:invite_code;";
            $vars = array(
                ':invite_code'=>$invite_code
            );
            $ps = $this->execute($q, $vars);
            return $this->getUpdateCount($ps);
        }




